<?php
session_start();
include_once __DIR__ . "/classes/AlefCore.php";
if (empty($_COOKIE["aq-tamper-id"])) {
    if (empty($_SESSION["aq-tamper-id"])) {
        $aqTamperId = uniqid("aq");
    } else {
        $aqTamperId = $_SESSION["aq-tamper-id"];
    }
    $_SESSION["aq-tamper-id"] = $aqTamperId;
    setcookie("aq-tamper-id", $aqTamperId, 10 * 365 * 24 * 3600 + time(), "/");
} else {
    $aqTamperId = $_COOKIE["aq-tamper-id"];
}
$aqJSON = getAQJson();
$aqAction = $_REQUEST["aq-action"] ?? null;
$aqResponse = $_REQUEST["aq-response"] ?? null;
$aqResponseNum = $_REQUEST["aq-response-num"] ?? null;

$tamperFileName = AlefCore::getTamperFileName($aqAction, $aqTamperId);

$func = $_REQUEST["func"] ?? null;

switch ($func) {
    case "save":
        if (!empty($aqResponse) && !empty(json_decode($aqResponse,true))) {
            file_put_contents($tamperFileName, $aqResponse);
        }
        break;
    case "delete":
        if (file_exists($tamperFileName)) {
            unlink($tamperFileName);
        }
        break;
}

$requests = getRequests($aqJSON, $aqTamperId);
$requestOptions = getOptions($requests, $aqAction);
?>
    <html>
    <head>
        <title><?php echo "Tamper ". $aqJSON["name"]?></title>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
        <script>
            $(document).ready(function () {
                $('#aq-action').change(function () {
                    $('#aq-response-num').val([]);
                    this.form.submit();
                });

                $('.list-group-item').click(function () {
                    $('#aq-response-num').val($(this).data('content'));
                    $('#main-form').submit();
                });
                $('#aq-response').on('change keyup paste', function () {
                    $('#aq-response').css('background-color', '#FFFF33');
                });

                $('#aq-response-num').change(function(){
                    this.form.submit();
                });

                $('#btn-save').click(function (e) {
                    e.preventDefault();
                    validateAndSaveJson();
                });

                $("#aq-response").keydown(function (event) {
                    if((event.metaKey || event.ctrlKey) &&  event.keyCode == 13)
                    {
                        validateAndSaveJson();
                    }
                });

                $('#btn-delete').click(function () {
                    $('#aq-response-num').val([]);
                    $('#func').val('delete');
                    this.form.submit();
                });

                $('#aq-response-json-renderer').jsonViewer(JSON.parse($("#aq-response").val()));

                $('#btn-toggle-view-type').click(function (e) {
                    e.preventDefault();
                    let btnState = $('#btn-toggle-view-type').data('content');
                    if(btnState==0 && validateJson())
                    {
                        $('#btn-toggle-view-type').text('Textarea');
                        $('#btn-toggle-view-type').data('content','1');
                        $('#aq-response-json-renderer').jsonViewer(JSON.parse($("#aq-response").val()));
                        $('#aq-response-json-renderer').show();
                        $('#aq-response').hide();
                    }
                    else
                    {
                        $('#btn-toggle-view-type').text('JSONViewer');
                        $('#btn-toggle-view-type').data('content','0');
                        $('#aq-response-json-renderer').hide();
                        $('#aq-response').show();
                    }

                });

                function validateAndSaveJson()
                {
                    if(validateJson()===true) {
                        $('#aq-response-num').val([]);
                        $('#func').val('save');
                        $('#main-form').submit();
                    }
                }

                function validateJson()
                {
                    try {
                        let c = $.parseJSON($("#aq-response").val());
                    }
                    catch (err) {
                        $('#aq-response').css('background-color', '#F08080');
                        setTimeout(function() {
                            alert("Невалидный json");
                        },100)
                        return false;
                    }
                    return true;
                }

            });
        </script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery.json-viewer@1.4.0/json-viewer/jquery.json-viewer.css">
        <script src="https://cdn.jsdelivr.net/npm/jquery.json-viewer@1.4.0/json-viewer/jquery.json-viewer.js"></script>

    </head>
    <body>
    <div class="container-fluid height-full" style="max-height: 100%;height:95%">
        <div class="row" style="background-color:#c8e7ff;padding: 10px">
            <h3>Подмена ответов AlefQuery для отладки</h3>
        </div>
        <div class="row flex-fill" style="background-color:#c8e7ff;">
            <div class="col text-wrap" >
                <form method="post" id="main-form">
                    <div class="form-group">
                        <div>Проверить результат запроса:<a href="../index.php?alef_action=!<?php echo $aqAction ?>&aq_tamper_id=<?php echo $aqTamperId ?>" target="_blank" rel="noopener noreferrer">?alef_action=!<?php echo $aqAction ?>&aq_tamper_id=<?php echo $aqTamperId ?></a>

                            <br/>
                            <br/><b>SWIFT:</b><br/>Добавьте строчку <code><b>AQ.tamperId = "<?php echo $aqTamperId ?>"</b></code> в функцию
                            func application(_ application: UIApplication, didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?) -> Bool {
                            в файле <b>AppDelegate.swift</b></div>
                        <input type="hidden" class="form-control" id="aq-tamper-id" name="aq-tamper-id" value="<?php echo $aqTamperId ?>">
                    </div>
                    <div class="form-group">
                        <label for="aq-action">Выберите запрос для подмены ответа:</label>
                        <select class="form-control" id="aq-action" name="aq-action">
                            <?php echo $requestOptions ?>
                        </select>
                    </div>
                    <br/>
                    <?php
                    if (!empty($aqAction))
                    {
                    ?>
                    <div>
                        <input type='hidden' name='func' value='' id='func'>
                        <div><h4><?php echo $aqAction; ?></h4></div>
                        <br/>
                        <?php
                        $response = $requests[$aqAction]["example_response"];
                        $tamperResponse = $requests[$aqAction]["tamper_response"] ?? null;
                        $aqSavedResponseNum = -1;

                        $hasTamperResponse = !empty($tamperResponse);

                        if($hasTamperResponse)
                        {
                            $deleteButton = "<button class='btn btn-danger float-right' id='btn-delete'><i class=\"fas fa-trash\"></i></button>";
                        }

                        if ($hasTamperResponse) {
                            for ($i=0;$i<count($response);$i++)
                            {
                                $item = $response[$i];
                                if(json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)==json_encode($tamperResponse,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                                {
                                    $aqSavedResponseNum = $i;
                                    break;
                                }
                            }
                            if($aqSavedResponseNum==-1) {
                                $response[] = $tamperResponse;
                                $aqSavedResponseNum = count($response) - 1;
                            }

                            if ($aqResponseNum === null) {
                                $aqResponseNum = $aqSavedResponseNum;
                            }

                        } else {
                            if ($aqResponseNum === null) {
                                $aqResponseNum = 0;
                            }
                        }
                        echo "</div>";
                        echo "<div>";
                        echo "<select class='form-control' id='aq-response-num' name='aq-response-num' style='display: none'>";
                        echo getResponseNumOptions($response, $aqResponseNum, $aqSavedResponseNum);
                        echo "</select>";
                        echo "</div>";

                        $listOptions = getResponseNumListOptions($response, $aqResponseNum, $aqSavedResponseNum);
                        $response = $response[$aqResponseNum];
                        $textAreaStyle = "";
                        if ($aqResponseNum == $aqSavedResponseNum) {
                            $textAreaStyle .= "background-color:#98FB98;";
                        }

                        $prettyJson = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                        ?>
                        <div class="row">
                            <div class="col-4">
                                <div style="padding-bottom: 10px"><button class='btn btn-primary' id='btn-save'>Сохранить</button><?php echo $deleteButton?></div>

                                <div class="list-group" id="list-tab" role="tablist">
                                    <?php echo $listOptions;?>
                                </div>
                            </div>
                            <div class="col-8">
                                <div>
                                    <div style="padding-bottom: 10px"><button class='btn btn-primary' id='btn-toggle-view-type' data-content='0'>JSONViewer</button></div>
                                    <textarea rows=20 style='<?php echo $textAreaStyle ?>' class='form-control' name='aq-response' id='aq-response'><?php echo $prettyJson ?></textarea>
                                    <pre id="aq-response-json-renderer" style="display: none;"><?php echo $prettyJson ?></pre>
                                </div>
                            </div>
                        </div>
                </form><?php } ?>
            </div>
        </div>
    </div>
    </div>
    </body>
    </html>
<?php

function getResponseNumListOptions($responses, $responseNum, $savedResponseNum)
{
    $options = "";

    foreach ($responses as $key => $value) {
        $selected = "";
        $title = $key . " " . ($value["tamper_name"] ?? "");

        if ($key == $responseNum) {
            $selected = "active";
        }

        if ($key == $savedResponseNum) {
            $title .= " (текущий ответ)";
        }

        $options .= "<a class='list-group-item list-group-item-action {$selected}' href='#{$key}' data-content='{$key}'>{$title}</a>";
    }

    return $options;
}

function getResponseNumOptions($responses, $responseNum, $savedResponseNum)
{
    $options = "";

    foreach ($responses as $key => $value) {
        $selected = "";
        $title = $key . " " . ($value["tamper_name"] ?? "");

        if ($key == $responseNum) {
            $selected = "selected";
        }

        if ($key == $savedResponseNum) {
            $title .= " (текущий ответ)";
        }

        $options .= "<option value=\"{$key}\" {$selected}>{$title}</option>";
    }

    return $options;
}

function getOptions($requests, $selected_action)
{
    $options = "<option value=''></option>";
    foreach ($requests as $key => $value) {
        $selected = "";
        if ($key == $selected_action) {
            $selected = "selected";
        }
        $overridden = "";
        if (isset($value["tamper_response"])) {
            $overridden = "* (установлен отладочный ответ)";
        }
        $options .= "<option value=\"{$key}\" {$selected}>{$key}{$overridden}</option>";
    }
    return $options;
}

function getRequests($json, $aq_debug_id = null)
{
    $res = [];

    foreach ($json["requests"] as $request) {
        $debugFileName = AlefCore::getTamperFileName($request["action_id"], $aq_debug_id);
        $resReq = [];

        $exampleResponse = $request["example_response"];
        if (array_values($exampleResponse) !== $exampleResponse) {
            $exampleResponse = [$exampleResponse];
        }
        if (file_exists($debugFileName)) {
            $resReq["tamper_response"] = json_decode(file_get_contents($debugFileName), true);
        }

        $resReq["example_response"] = $exampleResponse;
        $res[$request["action_id"]] = $resReq;
    }
    return $res;
}

function getAQJson()
{
    $re = '/<pre id=\'aas_pre\'[^>]*>(.*?)<\/pre>/m';
    $str = file_get_contents(__DIR__ . "/info.php");
    preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
    $json_str = $matches[0][1];
    $json = json_decode($json_str, true);
    return $json;
}
