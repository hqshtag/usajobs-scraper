window.addEventListener('load', () => {
	//store the tabs variable
	var tabs = document.querySelectorAll('ul.nav-tabs > li');

	for (let i = 0; i < tabs.length; i++) {
		tabs[i].addEventListener('click', switchTab);
	}

	function switchTab(event) {
		event.preventDefault();

		document.querySelector('ul.nav-tabs li.active').classList.remove('active');
		document.querySelector('.tab-pane.active').classList.remove('active');

		var clickedTab = event.currentTarget;
		var anchor = event.target;
		var activePaneId = anchor.getAttribute('href');

		clickedTab.classList.add('active');
		document.querySelector(activePaneId).classList.add('active');
	}
});

if (ujsUpdater.last_update) {
	var x = setInterval(function() {
		// Get today's date and time
		var now = new Date().getTime();

		// Find the distance between now and the count down date
		var distance = ujsUpdater.last_update * 1000 + 86400000 - now; //86400000 = 24 hours in ms

		// Time calculations for days, hours, minutes and seconds
		//var days = Math.floor(distance / (1000 * 60 * 60 * 24));
		var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
		var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
		var seconds = Math.floor((distance % (1000 * 60)) / 1000);

		// Display the result in the element with id="demo"
		if (document.getElementById('ujs-update-timer')) {
			document.getElementById('ujs-update-timer').innerHTML =
				hours + ':' + minutes + ':' + seconds + ' until next update';
		}

		// If the count down is finished, write some text
		if (distance <= 0) {
			clearInterval(x);
			location.reload();
			if (document.getElementById('ujs-update-timer')) {
				document.getElementById('ujs-update-timer').innerHTML = 'Update in progress';
			}
		}
	}, 1000);
}
