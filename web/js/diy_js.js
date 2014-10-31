function diy_tools () {
	var diy__hostname = "your server";
     	this.https_url = "https://"+diy__hostname;
     	this.wss_url = "wss://"+diy__hostname;
     	this.client_id = "username";
     	this.client_secret = "password";
     	this.device = "";
}
// ***GIT*** 
// ***GitGit*** 
// var data="grant_type=client_credentials&client_id="+username+"&client_secret="+password;
/*
 * get token from server
 * return access_token
 */
/*
diy_tools.prototype.postDevice = function()  {
    	var subject = this;
	$.ajax({
		type: "POST",
		url: this.https_url+'/api/adddevice',
		dataType: "json",
		data: {
			'grant_type': 'client_credentials', 
			'client_id': this.client_id, 
			'client_secret': this.client_secret
		},
		success: function(response) {
			//var result = $.parseJSON(response);
			var result = response;
			return result.access_token;
		},
		error: function(response) {
			var result =  $.parseJSON(response);
			return result;
			console.log(response);
		}
	});
}
*/
diy_tools.prototype.getToken = function()  {
    	var subject = this;
	return $.ajax({
		type: "POST",
		url: this.https_url+'/api/token',
		dataType: "json",
		data: {
			'grant_type': 'client_credentials', 
			'client_id': this.client_id, 
			'client_secret': this.client_secret
		}
	});
}

/*
 * open wss 
 * required access_token
 * return wss connection 
 */
diy_tools.prototype.wss_connect = function()  {
    	var subject = this;
	var conn = new ab.Session(this.wss_url+'?access_token='+this.access_token,
		function() {
			var device = new Object();
			device.access_token = subject.access_token;
			device.name = subject.device;
			devicestr = JSON.stringify(device);
			conn.subscribe(subject.device, function(topic, data) {
				console.log('device data:"' + topic + '" : ' + data.a+data.b+data.c);
			});
		},
		function() {
			console.warn('WebSocket connection closed');
		},
		{
			'skipSubprotocolCheck': true
		}
	);
}

/*
 * get devices from server
 * required access_token
 * return user devices 
 */
diy_tools.prototype.getDevices = function()  {
    	var subject = this;
	return $.ajax({
		type: "GET",
		url: this.https_url+'/api/devices',
		dataType: "json",
		data: {
			'access_token': this.access_token
		}
	});
}



