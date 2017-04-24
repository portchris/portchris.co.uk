<?php 
$webroot = dirname(__FILE__) . '/../../../'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Laravel</title>
	<!-- 1. Load libraries -->
	<!-- Polyfill(s) for older browsers -->
	{{ Html::script('https://code.angularjs.org/2.0.0-beta.2/angular2-polyfills.js') }}
	{{ Html::script('https://code.angularjs.org/tools/system.js') }}
	{{ Html::script('https://code.angularjs.org/tools/typescript.js') }}
	{{ Html::script('https://code.angularjs.org/2.0.0-beta.2/Rx.js') }}
	{{ Html::script('https://code.angularjs.org/2.0.0-beta.2/angular2.dev.js') }}
	{{ Html::script('https://code.angularjs.org/2.0.0-beta.2/http.dev.js') }}
	<script>
		System.config({
			transpiler: 'typescript', 
			typescriptOptions: {
				emitDecoratorMetadata: true
			},
			packages: {
				'app': {
					defaultExtension: 'ts'
				}
			} 
		});
		System.import('app/main').then(null, console.error.bind(console));
	</script>
</head>
<body>
	{{ require_once($webroot . 'src/index.html') }}
</body>
</html>
