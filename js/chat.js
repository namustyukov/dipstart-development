$(document).ready(function() {
	$('.del').click(function(){
		self = $(this);
		$.get( $(this).attr('href'), function( data ) {
 			self.remove();
 		});
		return false;
	});

	$('.approve').click(function(){
		self = $(this);
		$.get( $(this).attr('href'), function( data ) {
 			self.remove();
 		});
		return false;
	});

	$('.request').click(function(){
		var userId = parseInt($(this).attr('user'));
		var userName = $(this).attr('username');
		$('#recipient').val(userId);

		$('#msgLabel').text('Сообщение для '+userName);
		return false;
	});

	$('.setexecutor').click(function(){
		$.get( $(this).attr('href'), function( data ) {
 			$('.setexecutor').remove();
 			$('.delexecutor').remove();
 		});
		return false;
	});

	$('.delexecutor').click(function(){
		$.get( $(this).attr('href'), function( data ) {
 			$('.delexecutor').remove();
 		});
		return false;
	});

	$('.readdress').click(function(){
		self = $(this);
		$.get( $(this).attr('href'), function( data ) {
 			self.remove();
 		});
		return false;
	});
	
	function view_chat(name, message, id, cost/* = null*/){
	    message = Trim(message);
        if (message.length<=0) return false;
        var role = parseInt($('div#message_send').data('role'));
        var order=$('#order').val();
        var message_send = $('div#message_send').data('messageSend');
        var html_message_send = 
            '<div class="message_send info"><div class="message_send flash-success"><a>'+message_send+'</a></div></div>';
        $('#message_send div.message_send').remove();
        $.post('/project/chat?orderId='+order,{
			ProjectMessages:{
				id: id,
				message: message,
				recipient: name,
				order: order,
				cost: cost
			}
        },function(data){
              $('#chat').html(data);
		      if (cost != null) $('div.post.chtpl0-msg.author-message').filter(':last').before(($('div.take-block').data('message')));
              $('.chat-view').scrollTop(10000);
              $('#message').val('');
			  $('<style>div#chatWindow::before{display:none} div#chatWindow::after{display:none}</style>').appendTo('head');
              if (role==1) {
                $('#message_send').append(html_message_send);
                $('.message_send').fadeIn(400,function(){
                    setTimeout(function() {
                      $('.message_send').fadeOut(400);
                    },5000);
                });
              }
            
        });
	};

	
    $('.btn-chat').click(function(){
        view_chat( this.name, $('#message').val(), 0);
		return false;
    });
    $('#salary-to-chat').click(function(){
		var cost = $('input#cost').val();
		view_chat( 'author_to_manager', $(this).val(), 0, cost);
		$('input#cost').val('');
		$("body,html").animate({scrollTop: $('#chatWindow').offset().top}, 500);
		return false;
    });
    $('.chat-edit').click(function(){
		var text = document.getElementsByClassName("text");
		if (text.length > 0) {
			var step = $('#chat-edit').attr('step');
			step++;	step &= 1;
			$('#chat-edit').attr( 'step', step);
			if (step)	{ 
				text = text[text.length-1].innerHTML;
				text = text.replace(/<br>/g,"\r\n");
				$('#edit-message').val(text);
				$('#div-edit-message').css('display', 'block');
				$('#chat-edit').val('Сохранить'); 
				//$('#chat-edit').css('display', 'block');

				//прячем текстовое поле и кнопки отправить
				$('.chtpl0-submit1').css('display', 'none');
				$('.chtpl0-submit2').css('display', 'none');
				$('.chat-text-area').css('display', 'none');
				
				$('.chat-view').scrollTop(10000);
			} else { 
				$('#chat-edit').val('Редактирование последнего сообщения'); 
				$('#div-edit-message').css('display', 'none');
				view_chat( 'customer', $('#edit-message').val(),text[text.length-1].id);

				//показываем текстовое поле и кнопки отправить
				$('.chtpl0-submit1').css('display', 'inline-block');
				$('.chtpl0-submit2').css('display', 'inline-block');
				$('.chat-text-area').css('display', 'block');
			}
		}
        return false;
    });
	$('.chat-view').scrollTop(10000);

	$('.message-buttons-items .btn-message').click(function(){
		$('#message').val($(this).parent().find('.message-buttons-text-hidden').html());
	});
});
function zakaz_done(part_id) { /* Stage is ready */
		var orderId = $('#order').val();
		$.ajax({
			type: "POST",
			url: 'zakazParts/status'
			, data: 'cmd=done&id='+part_id+'&status_id=+1'
			, success: function(answer) {
				if(answer=='Wrong base status'){
					alert(answer);
				}else{
					document.getElementById('zakaz-done-'+part_id).style.display = 'none';
					document.getElementById('partStatus-status-'+part_id).innerHTML = answer;
					$('#stage-'+part_id+' .partStatus').show();
				}
			}
		});
        return false;
};

function Trim(s) {
    s = s.replace(/^ +/,'');
    s = s.replace(/ +$/,'');
    return s;
}
