(function ($, Drupal, drupalSettings) {
	var botLogo='';
	var botuserlogo = '';
	var accesstoken = '';
	Drupal.behaviors.DrupalbotBehavior = {
		attach: function (context, settings) {
			// Accessing Config Variables from setting from 'drupalSettings';
			var botLogo = drupalSettings.chatbot.chatbotdata.botlogo;
			var botuserlogo = drupalSettings.chatbot.chatbotdata.botuserlogo;
			var accesstoken = drupalSettings.chatbot.chatbotdata.access_token;
		}
	};

	var botLogo = drupalSettings.chatbot.chatbotdata.botlogo;
	var botuserlogo = drupalSettings.chatbot.chatbotdata.botuserlogo;
	var accesstoken = drupalSettings.chatbot.chatbotdata.access_token;

	var local = {};
	local.avatar = botuserlogo;

	var remote = {};
	remote.avatar = botLogo;

	var accessToken = accesstoken,
	baseUrl = "https://api.api.ai/v1/",
	$speechInput,
	$recBtn,
	recognition,
	messageRecording = "Recording...",
	messageCouldntHear = "I couldn't hear you, could you say that again?",
	messageInternalError = "Oh no, there has been an internal server error",
	messageSorry = "I'm sorry, I don't have the answer to that yet.";

	function startRecognition() {
		recognition = new webkitSpeechRecognition();
		recognition.continuous = false;
		recognition.interimResults = false;

		recognition.onstart = function(event) {
			$speechInput.val(messageRecording);
			respond(messageRecording);
			updateRec();
		};
		recognition.onresult = function(event) {
			recognition.onend = null;

			var text = "";
			for (var i = event.resultIndex; i < event.results.length; ++i) {
				text += event.results[i][0].transcript;
			}

			if (checkIfEmailInString(text)){
				text = text.replace(/ /g, '');
				text = text.toLowerCase();
				text = text.trim();
			}
			$speechInput.val(" ");
			setInput(text);
			stopRecognition();
		};
		recognition.onend = function() {
			respond(messageCouldntHear);
			stopRecognition();
		};
		recognition.lang = "en-US";
		recognition.start();
	}

    function stopRecognition() {
		if (recognition) {
			recognition.stop();
			recognition = null;
			$speechInput.attr('placeholder','Type a message or speak').val("").focus();
			$($recBtn).css("background", "#337ab7");
		}
		updateRec();
	}

    function switchRecognition() {
		if (recognition) {
			stopRecognition();
			} else {
			startRecognition();
		}
	}

    function setInput(text) {
		insertChat("local", text);
		queryBot(text);
	}

    function updateRec() {
		$recBtn.text(recognition ? "Stop" : "Speak");
	}

    function respond(val) {
		if (val == "") {
			val = messageSorry;
		}

		if (val !== messageRecording) {
			console.log($keyPress);
			var msg = new SpeechSynthesisUtterance();
			msg.voiceURI = "native";
			msg.text = val;
			msg.lang = "en-US";
			window.speechSynthesis.speak(msg);
		}

	}

	function formatTime(date) {
		var hours = date.getHours();
		var minutes = date.getMinutes();
		var ampm = hours >= 12 ? 'PM' : 'AM';
		hours = hours % 12;
		hours = hours ? hours : 12;
		minutes = minutes < 10 ? '0'+minutes : minutes;
		var strTime = hours + ':' + minutes + ' ' + ampm;
		return strTime;
	}

	function checkIfEmailInString(text) {
        var re = /(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(".+\"))\s*@\s*((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/;
        return re.test(text);
	}

	function insertChat(who, text){
		var control = "";
		var date = formatTime(new Date());

		if (who == "local"){

			control = '<li style="width:100%;">' +
			'<div class="msj-rta macro">' +
			'<div class="text text-r">' +
			'<p>'+text+'</p>' +
			'<p><small>'+date+'</small></p>' +
			'</div>' +
			'<div class="avatar" style="padding:0px 0px 0px 10px !important"><img class="img-circle" style="width:100%;" src="'+local.avatar+'" /></div>' +
			'</li>';
			}else{
			control = '<li style="width:100%">' +
			'<div class="msj macro">' +
			'<div class="avatar"><img class="img-circle" style="width:100%;" src="'+ remote.avatar +'" /></div>' +
			'<div class="text text-l">' +
			'<p>'+ text +'</p>' +
			'<p><small>'+date+'</small></p>' +
			'</div>' +
			'</div>' +
			'</li>';
		}
		$("#messages").append(control);
		var objDiv = document.getElementById("messages");
		objDiv.scrollTop = objDiv.scrollHeight;
	}

	$("#chat-panel").on('click',function(){
		$(".innerframe").toggle();
	});

	function resetChat(){
		$("#messages").empty();
	}

	$(".mytext").on("keyup", function(e){
		if (e.which == 13){
			$keyPress=e.which;
			var text = $(this).val();
			if (text !== ""){
				insertChat("local", text);
				$(this).val('');
				queryBot(text)
			}
		}
	});

	function getIntro()
	{
		firstqueryBot('Hi');
	}

	function firstqueryBot(text) {
		$.ajax({
			type: "POST",
			url: baseUrl + "query?v=20150910",
			contentType: "application/json; charset=utf-8",
			dataType: "json",
			headers: {
				"Authorization": "Bearer " + accessToken
			},
			data: JSON.stringify({ query: text, lang: "en", sessionId: "1234567890" }),
			success: function(data) {
				var textResult = data.result.fulfillment.speech;
				var matches = textResult.match(/href="([^"]*)/)
				if (matches){
					matches = textResult.match(/href="([^"]*)/)[1];
					var lochref = matches;
					window.location.assign(lochref);
				}
				insertChat("remote",data.result.fulfillment.speech);
			},
			error: function() {
				insertChat("remote","Internal Server Error! Sorry For Unavailability");
			}
		});
	}

	function queryBot(text) {
		$.ajax({
			type: "POST",
			url: baseUrl + "query?v=20150910",
			contentType: "application/json; charset=utf-8",
			dataType: "json",
			headers: {
				"Authorization": "Bearer " + accessToken
			},
			data: JSON.stringify({ query: text, lang: "en", sessionId: "1234567890" }),
			success: function(data) {
				var textResult = data.result.fulfillment.speech;
				var matches = textResult.match(/href="([^"]*)/)
				if (matches){
					matches = textResult.match(/href="([^"]*)/)[1];
					var lochref = matches;
					window.location.assign(lochref);
				}
				insertChat("remote",data.result.fulfillment.speech);
				if(!$keyPress){
					respond(data.result.fulfillment.speech);
				}
			},
			error: function() {
				insertChat("remote","Internal Server Error");
			}
		});
	}

	$(document).ready(function() {
		if(sessionStorage.getItem('messageContent')){
			$('#messages').html(sessionStorage.getItem('messageContent'));
		}
		else{
			getIntro();
		}
		$speechInput = $("#speech");
		$recBtn = $("#rec");
		$keyPress=0;
		$recBtn.on("click", function(event) {
			$keyPress=0;
			$(this).css("background", "#faa635");
			switchRecognition();
		});
	});

	$(window).bind('beforeunload',function(){
		//save info somewhere
		var messageContent='';
		messageContent = document.getElementById("messages").innerHTML;
		sessionStorage.setItem('messageContent', messageContent);

	});
}(jQuery, Drupal, drupalSettings));