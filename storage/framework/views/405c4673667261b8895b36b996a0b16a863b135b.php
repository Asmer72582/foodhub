<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <!-- REQUIRED META TAGS -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- FONTS -->
    <link rel="stylesheet" href="<?php echo e(asset('themes/default/fonts/fontawesome/fontawesome.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('themes/default/fonts/lab/lab.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('themes/default/fonts/typography/public/public.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('themes/default/fonts/typography/rubik/rubik.css')); ?>">

    <!-- CUSTOM STYLE -->
    <link rel="stylesheet" href="<?php echo e(asset('themes/default/css/custom.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(mix('css/app.css')); ?>">
    <!-- PAGE TITLE -->
    <title><?php echo e(Settings::group('company')->get('company_name')); ?></title>

    <!-- FAV ICON -->
    <link rel="icon" type="image" href="<?php echo e($favicon); ?>">

    <!-- PWA MANIFEST -->
    <link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="<?php echo e(Settings::group('company')->get('company_name')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e($favicon); ?>">
    <meta name="theme-color" content="#ff9100">


    <?php if(!blank($analytics)): ?>
        <?php $__currentLoopData = $analytics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $analytic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(!blank($analytic->analyticSections)): ?>
                <?php $__currentLoopData = $analytic->analyticSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($section->section == \App\Enums\AnalyticSection::HEAD): ?>
                        <?php echo $section->data; ?>

                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
</head>

<body>
    <?php if(!blank($analytics)): ?>
        <?php $__currentLoopData = $analytics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $analytic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(!blank($analytic->analyticSections)): ?>
                <?php $__currentLoopData = $analytic->analyticSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($section->section == \App\Enums\AnalyticSection::BODY): ?>
                        <?php echo $section->data; ?>

                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    <div id="app">
        <default-component />
    </div>

    <?php if(!blank($analytics)): ?>
        <?php $__currentLoopData = $analytics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $analytic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(!blank($analytic->analyticSections)): ?>
                <?php $__currentLoopData = $analytic->analyticSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($section->section == \App\Enums\AnalyticSection::FOOTER): ?>
                        <?php echo $section->data; ?>

                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    <script>
        const APP_URL = "<?php echo e(env('MIX_HOST')); ?>";
        const APP_KEY = "<?php echo e(env('MIX_API_KEY')); ?>";
        const GOOGLE_TOKEN = "<?php echo e(env('MIX_GOOGLE_MAP_KEY')); ?>";
        const APP_DEMO = "<?php echo e(env('MIX_DEMO')); ?>";
    </script>

    <script src="<?php echo e(mix('js/app.js')); ?>"></script>
    <script src="<?php echo e(asset('themes/default/js/jquery-v3.2.1.min.js')); ?>"></script>
    <script src="<?php echo e(asset('themes/default/js/drawer.js')); ?>"></script>
    <script src="<?php echo e(asset('themes/default/js/modal.js')); ?>"></script>
    <script src="<?php echo e(asset('themes/default/js/jqueryScript.js')); ?>"></script>
    <script src="<?php echo e(asset('themes/default/js/tabs.js')); ?>"></script>
    <script src="<?php echo e(asset('themes/default/js/jqueryDropdown.js')); ?>"></script>

    <!-- PWA SERVICE WORKER REGISTRATION -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);
                    })
                    .catch(err => {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>
</body>

</html>
<?php /**PATH /Users/asmerchougle/Desktop/foodhubwebsite/resources/views/master.blade.php ENDPATH**/ ?>