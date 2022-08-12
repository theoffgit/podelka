(function () {
	$('.select_json').click(function()
	{
		selectText('aas_pre');
	});
    $('.items li').each(function () {
        $(this).click(function () {
            var div = $(this).next('.in_li');
            if (div.css('display') == 'none') {
                $('.items li').removeClass("changed_li");
                $('.in_li').each(function () {
                    $(this).slideUp(500);
                });
                div.slideDown(500);
                $(this).addClass("changed_li");
            } else {
                $(this).removeClass("changed_li");
                div.slideUp(500);
            }
        })
    })

	$(document).on('click', '.copyAAS', function(e) {
		e.preventDefault();
		copyTextToClipboard($('#aas_pre').text());
		alert("Скрипт скопирован в ваш буфер обмена");
	})
})();

function copyTextToClipboard(text)
    {
      var textArea = document.createElement("textarea");
      textArea.style.position = 'fixed';
      textArea.style.top = 0;
      textArea.style.left = 0;
      textArea.style.width = '2em';
      textArea.style.height = '2em';
      textArea.style.padding = 0;
      textArea.style.border = 'none';
      textArea.style.outline = 'none';
      textArea.style.boxShadow = 'none';
      textArea.style.background = 'transparent';
      textArea.value = text;

      document.body.appendChild(textArea);
      textArea.focus();
      textArea.select();

      try
      {
        var successful = document.execCommand('copy');
        var msg = successful ? 'successful' : 'unsuccessful';
        console.log('Copying text command was ' + msg);
      } catch (err)
      {
        console.log('Oops, unable to copy');
      }

      document.body.removeChild(textArea);
    }



function selectText(containerid) {
        if (document.selection) {
            var range = document.body.createTextRange();
            range.moveToElementText(document.getElementById(containerid));
            range.select();
        } else if (window.getSelection) {
            var range = document.createRange();
            range.selectNode(document.getElementById(containerid));
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
        }

    }
