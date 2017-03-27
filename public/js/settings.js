jQuery("form").on('submit',function(){return false;});

jQuery("select[name='mode']").on('change',function(e) {
	$.post('/set/mode', {mode: jQuery(e.target).val()}, function() {
		$.get('/status.json',{},function(r) {
			console.log(r);
		});
	});
})
jQuery("#temperature").on('change',function(e) {
	$.post('/set/temperature', {temperature: jQuery(e.target).val()}, function() {
		$.get('/status.json',{},function(r) {
			console.log(r);
		});
	});
})

jQuery("#power_on").on('click',function(e) {
	$.post('/set/power', {power: 1}, function() {
		$.get('/status.json',{},function(r) {
			console.log(r);
		});
		e.target.siblings('button').removeClass('pure-button-disabled');
		e.target.addClass('pure-button-disabled');
		return false;
	});
});

jQuery("select[name='vane']").on('change',function(e) {
	$.post('/set/vane', {vane: jQuery(e.target).val()}, function() {
		$.get('/status.json',{},function(r) {
			console.log(r);
		});
	});
})

jQuery("#power_off").on('click',function(e) {
	$.post('/set/power', {power: 0}, function() {
		$.get('/status.json',{},function(r) {
			console.log(r);
		});
		e.target.siblings('button').removeClass('pure-button-disabled');
		e.target.addClass('pure-button-disabled');
		return false;
	});
})
