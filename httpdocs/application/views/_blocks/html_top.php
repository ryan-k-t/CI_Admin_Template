<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Template <?= isset($title) ? " | ".$title : "" ?></title>

        <? /* Standard Metadata */ ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">

		<?
		$fav_icon_directory = "/assets/images/favicons" ;?>
        <link rel="apple-touch-icon" sizes="57x57" href="<?=$fav_icon_directory?>/apple-touch-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="60x60" href="<?=$fav_icon_directory?>/apple-touch-icon-60x60.png">
		<link rel="apple-touch-icon" sizes="72x72" href="<?=$fav_icon_directory?>/apple-touch-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="76x76" href="<?=$fav_icon_directory?>/apple-touch-icon-76x76.png">
		<link rel="apple-touch-icon" sizes="114x114" href="<?=$fav_icon_directory?>/apple-touch-icon-114x114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="<?=$fav_icon_directory?>/apple-touch-icon-120x120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="<?=$fav_icon_directory?>/apple-touch-icon-144x144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="<?=$fav_icon_directory?>/apple-touch-icon-152x152.png">
		<link rel="apple-touch-icon" sizes="180x180" href="<?=$fav_icon_directory?>/apple-touch-icon-180x180.png">
		<link rel="icon" type="image/png" href="<?=$fav_icon_directory?>/favicon-32x32.png" sizes="32x32">
		<link rel="icon" type="image/png" href="<?=$fav_icon_directory?>/android-chrome-192x192.png" sizes="192x192">
		<link rel="icon" type="image/png" href="<?=$fav_icon_directory?>/favicon-96x96.png" sizes="96x96">
		<link rel="icon" type="image/png" href="<?=$fav_icon_directory?>/favicon-16x16.png" sizes="16x16">
		<link rel="manifest" href="<?=$fav_icon_directory?>/manifest.json">
		<link rel="mask-icon" href="<?=$fav_icon_directory?>/safari-pinned-tab.svg" color="#5bbad5">
		<meta name="msapplication-TileColor" content="#da532c">
		<meta name="msapplication-TileImage" content="<?=$fav_icon_directory?>/mstile-144x144.png">
		<meta name="theme-color" content="#ffffff">
		
		<? if (ENVIRONMENT != "development") : ?>
		<script>
			window.onerror = function (errorMsg, url, lineNumber) {
			    alert('There was a javascript error on the page. Please try again or reload the page. If you continue to have issues try disabling any browser extensions.')
			}
		</script>
		<? endif;?>

		<? //script to catch any references to $ before it's loaded -- will be called in the html_end.php ?>
		<script type='text/javascript'>(function(w,d,u){w.readyQ=[];w.bindReadyQ=[];function p(x,y){if(x=="ready"){w.bindReadyQ.push(y);}else{w.readyQ.push(x);}};var a={ready:p,bind:p};w.$=w.jQuery=function(f){if(f===d||f===u){return a}else{p(f)}}})(window,document)</script>


		<link rel="stylesheet" type="text/css" href="/assets/css/stylesheets/_fontawesome/font-awesome.css">
		<link rel="stylesheet" type="text/css" href="/assets/css/stylesheets/utility.css">

		<!-- select2 css -->
		<link rel="stylesheet" type="text/css" href="/assets/css/select2/select2.css">
		<link rel="stylesheet" type="text/css" href="/assets/css/select2/select2-bootstrap.css">

		<!-- datepicker css -->
		<link rel="stylesheet" type="text/css" href="/assets/css/external/bootstrap-datepicker.standalone.min.css">

		<!-- Sortable css -->
		<link rel="stylesheet" type="text/css" href="/assets/css/external/sortable/sortable-theme-light.css">

		<!-- core css -->
		<link rel="stylesheet" type="text/css" href="/assets/css/stylesheets/screen.css">

	</head>
	<body<?= isset($body_class) ? " class=\"".$body_class."\"" : ""; ?>>
