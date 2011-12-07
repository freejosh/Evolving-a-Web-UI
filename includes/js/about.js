$(document).ready(function() {
	
	$(".stats .table").hide();
	$('<a href="#">+</a>').prependTo("h3").click(function(e) {
		e.preventDefault();
		$(this).text(function(i, txt) {
			return (txt == "+") ? "-" : "+";
		}).parentsUntil("div").parent().find(".table").slideToggle();
	}).first().click();

});