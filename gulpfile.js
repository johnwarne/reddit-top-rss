var gulp         = require('gulp'),
    plumber      = require('gulp-plumber'),
    rename       = require('gulp-rename');
var autoprefixer = require('gulp-autoprefixer');
var concat       = require('gulp-concat');
var uglify       = require('gulp-uglify');
var sass         = require('gulp-sass');
var browserSync  = require('browser-sync');

gulp.task('browser-sync', function() {
  browserSync({
    proxy: "https://reddit-top-rss.test",
    notify: false,
    open: false
  });
});

gulp.task('bs-reload', function () {
  browserSync.reload();
});

gulp.task('styles', function(){
  gulp.src(['scss/main.scss'])
    .pipe(plumber({
      errorHandler: function (error) {
        console.log(error.message);
        this.emit('end');
    }}))
    .pipe(sass())
    .pipe(autoprefixer('last 2 versions'))
    .pipe(gulp.dest('dist/css'))
    .pipe(browserSync.stream({match: '**/*.css'}));
});

gulp.task('js', function() {
  return gulp.src([
    'node_modules/jquery/dist/jquery.min.js',
    'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js',
    'js/script.js',
  ])
  .pipe(concat('scripts.min.js'))
  .pipe(gulp.dest('dist/js'))
  .pipe(rename('scripts.min.js'))
  .pipe(uglify())
  .pipe(gulp.dest('dist/js'));
});

gulp.task('default', ['browser-sync'], function(){
  gulp.watch("scss/*.scss", ['styles']);
  gulp.watch("**/*.php", ['bs-reload']);
  gulp.watch("js/script.js", ['js', 'bs-reload']);
});
