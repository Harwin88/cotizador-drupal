'use strict'

const gulp = require("gulp")
//const sass = require("gulp-sass")
var sass = require('gulp-sass')(require('sass'));//sass-css

const { parallel } = require('gulp')

function css() {
    return gulp.src('./scss/style.scss')
        .pipe(sass())
        .pipe(gulp.dest('./css'))
}

function watchCss(){
    gulp.watch('./scss/*', parallel('css'))
}

exports.css = css
exports.watch = watchCss