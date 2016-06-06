var ProjectPayments = function(orderId) {
    var self = this;
    this.form = $('#project_payments');
    this.orderId = orderId;
    
    /* Привязка имен */
	this.p_price = this.form.find('.project_price_input');
	this.w_price = this.form.find('.work_price_input');
	this.t_receive = this.form.find('.to_receive_input');
	this.t_pay = this.form.find('.to_pay_input');
	this.payment_received = this.form.find('.payment_received');
	this.payed = this.form.find('.payment_payed');
	this.to_receive = this.form.find('.payment_to_receive');
	this.to_pay = this.form.find('.payment_to_pay');
    
    this.sendCosts = function() {
        var proj_price = self.p_price.val();
        var work_price = self.w_price.val();
		
        if (proj_price!='' || work_price!='') {
            $.post('/project/payment/savePayments', JSON.stringify({
            'order_id': self.orderId,
            'project_price': proj_price,
            'work_price': work_price,
        }), function (response) {
            if (response.data) {
                console.log(response.data);
            }
        }, 'json');}

    };
	
	this.sendRecive = function() {
        var receive = self.t_receive.val();
		
        $.post('/project/payment/savePayments', JSON.stringify({
            'order_id': self.orderId,
            'to_receive': receive,
        }), function (response) {
            if (response.data) {
                self.t_receive.val('');
                self.to_receive.text(response.data.to_receive);
            }
        }, 'json');

		if (receive)
			send_message(13,'Заказчику об оплате когда выставлен счет',receive);
		
    };
	
	this.sendPay = function() {
       var pay = self.t_pay.val();
		
        $.post('/project/payment/savePayments', JSON.stringify({
            'order_id': self.orderId,
            'to_pay': pay,
        }), function (response) {
            if (response.data) {
                self.t_pay.val('');
                self.to_pay.text(response.data.to_pay);
            }
        }, 'json');

		if (pay)
			send_message(24,'Исполнителю об оплате заказа',pay);
		
    };
	
	/*this.sendPayments = function() {
        var proj_price = self.p_price.val();
        var work_price = self.w_price.val();
        var receive = self.t_receive.val();
        var pay = self.t_pay.val();
		
        if (proj_price!='') {
            $.post('/project/payment/savePayments', JSON.stringify({
            'order_id': self.orderId,
            'project_price': proj_price,
            'to_receive': receive,
            'work_price': work_price,
            'to_pay': pay
        }), function (response) {
            if (response.data) {
                self.t_receive.val('');
                self.p_price.val(response.data.project_price);
                self.to_receive.text(response.data.to_receive);
                self.t_pay.val('');
                self.w_price.val(response.data.work_price);
                self.to_pay.text(response.data.to_pay);
            }
        }, 'json');}

		if ((pay==0) && (receive))
			send_message(13,'Заказчику об оплате когда выставлен счет',receive);
		if (pay>0)	
			send_message(24,'Исполнителю об оплате заказа',pay);

    };*/
    
	self.form.find('.send_user_payments').on('click', function() {
		self.sendPayments();
	});
	self.form.find('.sendToRecive').on('click', function() {
		self.sendRecive();
		
	});
	self.form.find('.sendToPay').on('click', function() {
		self.sendPay(); 
	});
	self.form.find('.send_costs').on('change', function() {
		self.sendCosts();
	});
    
    self.form.find('.send_managers_approve'). on('click', function() {
        $.post('/project/payment/managersApprove', JSON.stringify({
            'order_id': self.orderId
        }), function (response) {
            if (response.data) {
                self.payment_received.text(response.data.received);
                self.to_receive.text(0);
				if(response.data.error) alert(response.data.error);
            } else {
            }
        }, 'json');
		
    });
    
    self.form.find('.send_managers_cancel'). on('click', function() {
        $.post('/project/payment/managersCancel', JSON.stringify({
            'order_id': self.orderId
        }), function (response) {
            if (response.data) {
                self.to_receive.text(response.data.to_receive);
            } else {
            }
        }, 'json');
    });
}