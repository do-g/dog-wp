<style>
	header {
		display: none;
	}
	aside {
		display: none;
	}
	.high {
		height: 1600px;
		padding-top: 200px;
	}
	.holder {
		background: yellow;
		border: 3px solid #000;
		height: 500px;
		overflow: auto;
		box-sizing: border-box;
		position: relative;
	}
	.items {
		height: 1000px;
		padding-top: 300px;
	}
	.item {
		background: red;
		height: 100px;
	}
	.buts {
		position: fixed;
		top: 0;
		left: 0;
	}
	.buts.right {
		left: auto;
		right: 0;
	}
</style>
<div class="high">
	<div class="holder">
		<div class="items">
			<div class="item"></div>
		</div>
	</div>
	<nav class="buts left">
		<button onclick="jQuery(window).scrollTo('.holder')">scroll (top)</button>
		<button onclick="jQuery(window).scrollTo('.holder', {offset: 10})">scroll (top, 10px)</button>
		<button onclick="jQuery(window).scrollTo('.holder', {origin: 'closest'})">scroll (closest)</button>
		<button onclick="jQuery(window).scrollTo('.holder', {origin: 'closest', offset: 10})">scroll (closest, 10px)</button>
	</nav>
	<nav class="buts right">
		<button onclick="jQuery('.holder').scrollTo('.item')">scroll (top)</button>
		<button onclick="jQuery('.holder').scrollTo('.item', {offset: 10})">scroll (top, 10px)</button>
		<button onclick="jQuery('.holder').scrollTo('.item', {origin: 'closest'})">scroll (closest)</button>
		<button onclick="jQuery('.holder').scrollTo('.item', {origin: 'closest', offset: 10})">scroll (closest, 10px)</button>
	</nav>
</div>