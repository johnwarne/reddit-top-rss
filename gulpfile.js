// Initialize modules
// Importing specific gulp API functions lets us write them below as series() instead of gulp.series()
const { src, dest, watch, series, parallel, task } = require('gulp');
// Importing all the Gulp-related packages we want to use
const sourcemaps = require('gulp-sourcemaps');
const sass = require('gulp-sass');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
var replace = require('gulp-replace');
const browserSync = require('browser-sync');
const server = browserSync.create();
const tailwindcss = require('tailwindcss');
const atimport = require("postcss-import");
const purgecss = require("postcss-purgecss");
const postcssNesting = require("postcss-nesting");



// File paths
const files = {
    cssPath: 'css/styles.css',
    jsPath: 'js/vue-scripts.js'
}

// Sass task: compiles the style.css file into style.css
function cssTask(){
    return src(files.cssPath)
        // .pipe(sourcemaps.init()) // initialize sourcemaps first
        // .pipe(sass()) // compile SCSS to CSS
        .pipe(postcss([
            // atimport(),
            tailwindcss(),
            postcssNesting(),
            purgecss({
              content: ["**/*.php", "css/styles.css"],
              defaultExtractor: content =>
              content.match(/[\w-/:]+(?<!:)/g) || []
            }),
            // autoprefixer(),
            // cssnano()
        ])) // PostCSS plugins
        // .pipe(sourcemaps.write('.')) // write sourcemaps file in current directory
        .pipe(dest('dist/css'))
        .pipe(browserSync.stream({match: '**/*.css'}))
    ; // put final CSS in dist folder
}

// JS task: concatenates and uglifies JS files to script.js
function jsTask(){
    return src([
        files.jsPath
        //,'!' + 'includes/js/jquery.min.js', // to exclude any specific files
        ])
        .pipe(concat('all.js'))
        .pipe(uglify())
        .pipe(dest('dist/js')
    );
}

// Cachebust
function cacheBustTask(){
    var cbString = new Date().getTime();
    return src(['vue.php'])
        .pipe(replace(/cb=\d+/g, 'cb=' + cbString))
        .pipe(dest('.'));
}

// Watch task: watch CSS and JS files for changes
// If any change, run css and js tasks simultaneously
function watchTask(){
  browserSync.init({

    // proxy: sitename +'.test',
     // or if site is http comment out below block and uncomment line above
      proxy: 'https://reddit-top-rss.test',
            notify: false,
    open: false
 });
    watch(['css/**/*.css'],
        {interval: 1000, usePolling: true}, //Makes docker work
        series(
            parallel(cssTask),
            // cacheBustTask
        )
    );
    watch([files.jsPath],
        {interval: 1000, usePolling: true}, //Makes docker work
        series(
            parallel(jsTask),
            cacheBustTask
        )
    );
    watch('./*.php').on('change',browserSync.reload);
}

// Export the default Gulp task so it can be run
// Runs the css and js tasks simultaneously
// then runs cacheBust, then watch task
exports.default = series(
    parallel(cssTask, jsTask),
    cacheBustTask,
    watchTask
);