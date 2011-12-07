$(document).ready(function() {

	var $frame = $("#render");

	$frame.load(function() {
		var $doc = $(this.contentDocument);
		var maxScroll = 0.0;
		var scrollSaved = false;

		var frameHeight = $frame.outerHeight();
		var docHeight = $doc.outerHeight();
		var docVisible = frameHeight / docHeight;
		if (docVisible >= 1) maxScroll = 1.0;

		$doc.scroll(function() {
			var $this = $(this);

			frameHeight = $frame.outerHeight();
			docHeight = $this.outerHeight();
			docVisible = frameHeight / docHeight;
			if (docVisible >= 1) maxScroll = 1.0;

			var scroll = ($this.scrollTop() + frameHeight) / docHeight;
			if (scroll > maxScroll) maxScroll = scroll;

			if (maxScroll == 1.0 && !scrollSaved) {// save event when user has seen whole page
				scrollSaved = true;
				$.ajax({
					type: "POST",
					async: false,
					cache: false,
					url: "/save-analytics.php",
					data: {
						data: $.param({
							type: "scrollAll"
						})
					}
				});
			}
		});
		$doc.find("*").click(function(e) {
			e.stopPropagation();// stop bubbling so that only the most specific element saves the analytics
			$.ajax({
				type: "POST",
				async: false,
				cache: false,
				url: "/save-analytics.php",
				data: {
					data: $.param({
						type: "click",
						x: e.pageX,
						y: e.pageY,
						target: e.target.tagName,
						scroll: maxScroll
					})
				}
			});
		});

	});

});