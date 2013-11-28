// Isn't this SUCH a descriptive file name?

var msgs = document.getElementsByClassName('messages');
for(var i = 0; i < msgs.length; i++)
	msgs[i].style.display = "none";

var members = document.getElementsByClassName('members');
for(var i = 0; i < members.length; i++){
	members[i].style.cursor = "pointer";
	members[i].onclick = toggleMessages;
	var msg_count = msgs[i].getElementsByClassName('message').length;
	members[i].innerHTML += " <span class='count'>(" + msg_count + ")</span>";
}

function toggleMessages(e){
	var evt = e || window.event;
	if(evt){
		var element = evt.target || evt.srcElement;
		if(element.id){
			var id = element.id.substr(element.id.indexOf('_') + 1);
			var msgs = document.getElementById('messages_' + id);
			if(msgs.style.display === "none")
				msgs.style.display = "block";
			else
				msgs.style.display = "none";
		}
	}
}
