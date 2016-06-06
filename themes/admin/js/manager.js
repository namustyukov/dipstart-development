/* Akoch-ov */
function changes_approve(id, self){
	var value = $(self).data('moderate');
	console.log(value);
	$.post('/project/changes/approve?id='+id,{moderate: value});
	if(value) {
		$(self).data('moderate', 0);
		$(self).attr('title',$(self).data('s0'));
		$(self).removeClass('bg-gray');
		$(self).addClass('bg-green');
	} else {
		$(self).data('moderate', 1);
		$(self).attr('title',$(self).data('s1'));
		$(self).removeClass('bg-green');
		$(self).addClass('bg-gray');
	}
}
function changes_delete(id, self){
	$.post('/project/changes/delete?id='+id);
	$(self).parent().remove();
}
function stage_change_status( item_id, status ){
	if (status) {
		var status_id = status;
	} else {
		var status_id = document.getElementById('select-status-'+item_id).value;
	}
	var orderId = $('#order_number').text();
	$.ajax({
		type: "POST",
		url:'http://'+document.domain+'/project/zakazParts/status',
		data : 'cmd=status&status_id='+status_id+'&id='+item_id+'&orderId='+orderId,
		success: function(html) {
			/*html = BackReplacePlusesFromStr(html);*/
			if (html != 'null') {
				if(status_id == 2){
					var button = $('#stage-'+item_id+'-approve-button');
					button.prop( "disabled", false );
					button.removeClass('bg-gray');
					button.addClass('bg-green');
				}else {
					var button = $('#stage-'+item_id+'-approve-button');
					button.prop( "disabled", true );
					button.removeClass('bg-green');
					button.addClass('bg-gray');
					if(status_id == 3) document.getElementById("select-status-"+item_id).options[2].selected=true;
				}
				//console.log(html);
			}
		}
	});
}
/**
 * Created by coolfire on 08.05.15.
 */
function reload(){
    location.reload();
}
function add_part(orderid, title){
    $.post('/project/zakazParts/apiCreate', JSON.stringify({
        'orderId': orderid,

        'name': title
    }), function (response) {
        if (response.data) {
            reload();
        }
    }, 'json');
}
function delete_part(orderid) {
    $.post('/project/zakazParts/apiDelete', JSON.stringify({
        'id': orderid
    }), function (response) {
        if (response.data) {
            reload();
        }
    }, 'json');
}
function change_title(new_title,part_id){
    $.post('/project/zakazParts/apiEditPart', JSON.stringify({
        'id': part_id,
        'title': new_title
    }), function (response) {
        if (response.data.result) {
            $('#part_title_'.part_id).html(new_title);
        }
    }, 'json');
}
function change_comment(new_comment,part_id){
    $.post('/project/zakazParts/apiEditPart', JSON.stringify({
        'id': part_id,
        'comment': new_comment
    }), function (response) {}, 'json');
}
function send(url) { // Добавление доработки
    var formData = new FormData($("#up_file")[0]);
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        datatype: 'json',
        success: function (data,textStatus,errorThrown) {
            jQuery("#list_files").load(url.replace('add','list'));
            if (data.error) alert(data.error.text);
			if (data.success) window.location.reload();
        },
        error: function (data,textStatus,errorThrown) {
            alert('err: '+data+"\nstatus: "+textStatus+errorThrown);
        },
        processData: false,
        contentType: false,
        cache: false
    });
	
	if (url.indexOf('/project/changes/add') >= 0) {
		// уведомление об добавлении изменений
		var arr = url.split('/');
		$.post('/project/emails/send', JSON.stringify({
			'url': url,
			'orderId': arr[arr.length-1],
			'typeId': 23, // Emails::TYPE_23
			'name': 'Новое изменение'
		}), function (response) {
			if (response.data) {
//				reload();
			}
		}, 'json');
	};

    return false;
}
function stageFileApprove(obj){ /* Approve files in parts */
    var data=$(obj).data();
	$(obj).hide();
    $.post('/project/zakazParts/apiApprove', JSON.stringify({
        'data': data
    }), function (response) {
        if (response.data) {
            console.log(response);
			//console.log();
			$(obj).siblings("button").show();
			$(obj).siblings("button").removeClass("hidden");
			//if($(obj).hasClass('on')) $($('button[data-id="'+data['id']+']').find('.on[data-part_id="'+data['part_id']+'"]').removeClass("hidden")).show();
			//if($(obj).hasClass('off')) $($('button[data-id="'+data['id']+']').find('.off[data-part_id="'+data['part_id']+'"]').removeClass("hidden")).show();
        }
    }, 'json');
}

$(document).on("click", 'li span.deletefile', function(e) { /* Delete files in parts */
	var item = $(this).parent();
	var id = parseInt($(this).attr('id'));
	if(!isNaN(id)) {
		$.ajax({
		  type: "POST",
		  url:'http://'+document.domain+'/project/zakazParts/apiDeleteFile',
		  data : JSON.stringify({
			'id': id
		  }),
		  success: function(response) {
			 if(response.data && response.data.success) {
				 item.fadeOut(400,function(){
					 item.remove();
				 });
			 }
		  }
		});
	}
});

function approveFile(obj){
    var data=$(obj).data();
    $.post('/project/zakaz/apiApproveFile', JSON.stringify({
        'data': data
    }), function (response) {
        if (response.data)obj.remove();
    }, 'json');
}
/*Remove attachment file*/
function removeFile(obj){
    var data=$(obj).data();
    $.post('/project/zakaz/apiRemoveFile', JSON.stringify({
        'data': data
    }), function (response) {
        if (response.data)obj.remove();
    }, 'json');
}/*END Remove attachment file*/
function spam(orderid){
    $.ajax({
        url: '/project/zakaz/spam?orderId='+orderid,
        type: 'POST',
        datatype: 'json',
        success: function (data,textStatus,errorThrown) {
            if(data.error) alert(data.error);
        },
        error: function (data,textStatus,errorThrown) {
            console.log(data);
        }
    });

    alert('Рассылка запущена');
    return false;
}
function setApprove (id, type, number) {
    $.post('/project/payment/approveTransaction', JSON.stringify({
        'id': id,
        'type': type,
        'number': number
    }), function (response) {
        $.fn.yiiGridView.update('buh_transaction_in');
        $.fn.yiiGridView.update('buh_transaction_out');
    }, 'json');
}
function setReject (id) {
    $.post('/project/payment/rejectTransaction', JSON.stringify({
        'id': id
    }), function (response) {
        $.fn.yiiGridView.update('buh_transaction_in');
        $.fn.yiiGridView.update('buh_transaction_out');
    }, 'json');
}
function cancelPayment (id) {
    $.post('/project/payment/cancelTransaction', JSON.stringify({
        'id': id
    }), function (response) {
        $.fn.yiiGridView.update('buh_transaction_in');
        $.fn.yiiGridView.update('buh_transaction_out');
    }, 'json');
}
$( window ).load( function() {
    $('#Zakaz_notes, #Zakaz_author_notes').on('keyup',function(event){ // Сохранение заметок
        var data = $(this).val();
        var elid = $(this).attr('id');
        var id = $('#order_number').html();
        $.post('/project/zakaz/update?id='+id,
            {'data': data,'id':id,'elid': elid},
        function (response) {
            if (response.data)obj.remove();
        });
    });
	$('#Zakaz_dbmax_exec_date, #Zakaz_dbmanager_informed, #Zakaz_dbauthor_informed').on('change',function(event){ // Сохраниние сроков (три поля)
        var data = $(this).val();
        var elid = $(this).attr('id');
        var id = $('#order_number').html();
        $.post('/project/zakaz/update?id='+id,
            {'data': data,'id':id,'elid': elid},
        function (response) {
			console.log(response);
        });
    });
    $('#select_template').on('click',function(event){
        $.post('/templates/apiGetTemplate?id='+$('#templates').val(),function (response){
            tinymce.get('chat_message').setContent(response.data.text);
        });
    });
    if (document.getElementById("chat_message") != null) {
        tinymce.init({
            selector: "#chat_message",
            theme: "modern",
            menubar: false
        });
    }
    if (document.getElementById("#buh_transaction") != null)
        form.find('button.approve_payment').on('click', function(){
            self.setApprove($(this).attr('value'), $(this).attr('pay_method'));
        });
});


$( document ).ready( function() {
    $('#Zakaz_notes, #Zakaz_author_notes, form#zakaz-form input, form#zakaz-form textarea').on('keyup',function(event){
        var data = $(this).val();
        var elid = $(this).attr('id');
        var id = $('#order_number').html();
        $.post('/project/zakaz/update?id='+id,
            {'data': data,'id':id,'elid': elid},
        function (response) {
            if (response.data)obj.remove();
        });
    });
	$('#Zakaz_parent_id, form#zakaz-form select, form#zakaz-form input:checkbox').on('change',function(event){
        var data = $(this).val();
		var elid = $(this).attr('id');
		var id = $('#order_number').html();
		if($(this).attr('type')=='checkbox') {
			if($(this).prop('checked')) data = 1; else data = 0;
		}
		console.log(id+' '+elid+' '+data);
        //var elid = $(this).attr('id');
        //var id = $('#order_number').html();
        $.post('/project/zakaz/update?id='+id,
            {'data': data,'id':id,'elid': elid},
        function (response) {
			console.log(response);
            //if (response.data)obj.remove();
        });
    });

    $('input#technicalspec').change(function(){
        var val = $(this).prop('checked') ? 1 : 0;
        var orderId = $(this).data('id');
        $.post('/project/zakaz/setTechSpec', {orderId: orderId, val: val}, function (status){
            switch (status) {
                case 'no_users':
                    alert('Нет тех.руководителей');
                    break;

                case 'send_email':
                    alert('Рассылка произведена');
                    break;
            }
        });
    });
    
    //var posts = $('div#chatWindow');
    //console.log(posts);
});

$( document ).ready( function() {
    var arrow = 'fa-angle-down fa-lg';
    
    $('div.change-row div.panel-heading .panel-title a').append('<i style="font-size: 1.8em!important; padding-left: 4px" class="fa fa-angle-up fa-lg">');
    $('div.zero-edge').not('.change-row').find('div.panel-heading .panel-title a').append('<i style="font-size: 1.8em!important;" class="fa fa-angle-down fa-lg">');
    
    $('div.info-block div.panel-heading a').on('click', function() {
		
		$('#Zakaz_dbdate').attr('disabled', 'true' );
		document.getElementById('Zakaz_dbdate').style.color = '#000000';
		document.getElementById('Zakaz_dbdate').style.background = '#FFFFFF';
		
        if (arrow == 'fa-angle-down fa-lg') {
            arrow = 'fa-angle-up fa-lg';
            $('div.info-block div.panel-heading a i').removeClass('fa-angle-down fa-lg').addClass(arrow);
			
        } else {
            arrow = 'fa-angle-down fa-lg';
            $('div.info-block div.panel-heading a i').removeClass('fa-angle-up fa-lg').addClass(arrow);
        }
    });
    
    $('div.zero-edge div.panel-heading .panel-title a').on('click', function() {
        if ($(this).children('i').hasClass('fa-angle-down fa-lg')) {
            $(this).children('i').removeClass('fa-angle-down fa-lg').addClass('fa-angle-up fa-lg');
        } else {
            $(this).children('i').removeClass('fa-angle-up fa-lg').addClass('fa-angle-down fa-lg');
        }
    });
    
/*<<<<<<< HEAD*/
    var arrow = 'fa-angle-up';
    
    contactSectionButton = $('div.contactme');
    contactSectionButton.on('click', function() {
        if (arrow == 'fa-angle-up') {
            arrow = 'fa-angle-down';
            $('.fa').removeClass('fa-angle-up').addClass(arrow);
        } else {
            arrow = 'fa-angle-up';
            $('.fa').removeClass('fa-angle-down').addClass(arrow);
        }
        $('section.contact-section').slideToggle();
    });
	////----------------
    
    var iframeP = $('body.mce-content-body p');
    console.log(iframeP);

});
