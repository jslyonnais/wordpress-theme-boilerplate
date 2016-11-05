'use strict';

////////////////////////////////////////
//	STYLES DEPENDENCIES
////////////////////////////////////////

var gulp        = require('gulp'),
    gulpif      = require('gulp-if'),
    gulpkss     = require('gulp-kss'),
    util        = require('gulp-util'),
    sass        = require('gulp-sass'),
    rename      = require('gulp-rename'),
    notify      = require('gulp-notify'),
    sourcemaps  = require('gulp-sourcemaps'),
    autoprefix  = require('gulp-autoprefixer'),
    fileinclude = require('gulp-file-include');

////////////////////////////////////////
//	IMAGES DEPENDENCIES
////////////////////////////////////////

var imageOptim  = require('gulp-imageoptim'),
    svgstore    = require('gulp-svgstore'),
    svgmin      = require('gulp-svgmin'),
    cheerio     = require('gulp-cheerio');

////////////////////////////////////////
//	UTILS DEPENDENCIES
////////////////////////////////////////

var uglify      = require('gulp-uglify'),
    concat      = require('gulp-concat');

////////////////////////////////////////
//	CONFIGS
////////////////////////////////////////

var config = {
    production: !util.env.production
};

var path = {
    src: 'ressources/src/',
    dist: 'ressources/dist/'
}

////////////////////////////////////////
//	STYLES TASKS
////////////////////////////////////////

gulp.task('styles', function() {
    return gulp.src(path.src + 'css/styles.scss')
        .pipe(gulpif(
            config.production,
            sourcemaps.init() ))                    // Initialise sourcemaps prior to compiling SASS
        .pipe(sass({outputStyle: 'compressed'}))    // Compile SASS
        .pipe(rename({basename: 'styles.min'}))     // Rename styles.scss file to styles.min.css
        .pipe(autoprefix({
            browsers: ['last 2 versions']
        }))                                         // Run compiled CSS through autoprefixer
        .pipe(gulpif(
                config.production,
                sourcemaps.init({loadMaps: true}))) // Reinitialise sourcemaps, loading inline sourcemap
        .pipe(gulpif(
            config.production,
            sourcemaps.write('./')))                // Write sourcemap
        .pipe(gulp.dest(path.dist + 'css'))         // Write CSS file to desitination path
        .pipe(gulpif(
            config.production,
            notify('👌 STYLES task completed')));   // Notify alert for dev
});



////////////////////////////////////////
//	SCRIPTS TASKS
////////////////////////////////////////
gulp.task('scripts-internal', function() {
    return gulp.src(path.src + 'js/*.js')
        .pipe(gulpif( config.production, sourcemaps.init() ))
        .pipe(fileinclude())
        .pipe(uglify())
        .pipe(concat('scripts.min.js'))
        .pipe(gulpif( config.production, sourcemaps.init({loadMaps: true}) ))
        .pipe(gulpif( config.production, sourcemaps.write('./') ))
        .pipe(gulp.dest(path.dist + 'js'))
        .pipe(gulpif( config.production, notify('🖊 INTERNAL SCRIPTS task completed') ));
});

gulp.task('scripts-vendors', function() {
    return gulp.src(path.src + 'js/vendors/**/*.js')
        .pipe(gulpif( config.production, sourcemaps.init() ))
        .pipe(fileinclude())
        .pipe(uglify())
        .pipe(concat('vendors.min.js'))
        .pipe(gulpif( config.production, sourcemaps.init({loadMaps: true}) ))
        .pipe(gulpif( config.production, sourcemaps.write('./') ))
        .pipe(gulp.dest(path.dist + 'js'))
        .pipe(gulpif( config.production, notify('🖊 VENDORS SCRIPTS task completed') ));
});



////////////////////////////////////////
//	IMAGES TASKS
////////////////////////////////////////

gulp.task('images', function() {
    return gulp.src(path.src + 'images/**/*.{png,jpg,gif}')
        .pipe(gulpif( config.production, imageOptim.optimize() ))
        .pipe(gulp.dest(path.dist + 'images'))
        .pipe(gulpif( config.production, notify('🗼 IMAGES task completed') ));
});

gulp.task('svgstore', function() {
    return gulp.src(path.src + 'images/**/*.svg')
        .pipe(rename({ prefix: 'icon-' }))
        .pipe(svgstore({ inlineSvg: true }))
        .pipe(cheerio(function ($) {
            $('svg').attr('style',  'display:none');
        }))
        .pipe(rename("sprite.svg"))
        .pipe(gulp.dest(path.dist + 'images/'));
});

gulp.task('svgmin', function() {
    return gulp.src(path.src + 'images/**/*.svg')
    .pipe(svgmin())
    .pipe(gulp.dest(path.dist + 'images/'));
});

gulp.task('svg',['svgstore','svgmin'], function() {
    console.log('🗼 SVG task completed');
});


////////////////////////////////////////
//	GULP TASKS
////////////////////////////////////////

// @see To launch production build, use `gulp --production`
gulp.task('default', ['styles', 'scripts-internal', 'scripts-vendors', 'images', 'svg'], function() {
    console.log('✅ Default Task Completed!');
});

gulp.task('watch', function() {
    gulp.watch(path.src + 'css/**/*',        ['styles']);                                // Watch .styles files
    gulp.watch(path.src + 'js/**/*',         ['scripts-internal', 'scripts-vendors']);   // Watch .js files
    gulp.watch(path.src + 'images/*',        ['images']);                                // Watch images files
    gulp.watch(path.src + 'images/svg/*',    ['svg']);                                   // Watch SVG files
});
