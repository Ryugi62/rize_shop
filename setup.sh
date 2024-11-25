#!/bin/bash

# 기본 폴더 경로 설정
BASE_DIR="C:/APM_Setup/htdocs"

# 루트 폴더 생성
mkdir -p "$BASE_DIR"

# index.php 생성
echo "<?php echo 'Hello, World!'; ?>" > "$BASE_DIR/index.php"

# app 폴더 구조 생성
mkdir -p "$BASE_DIR/app/Controllers"
mkdir -p "$BASE_DIR/app/Models"
mkdir -p "$BASE_DIR/app/Core"
mkdir -p "$BASE_DIR/app/Services"

# Controllers에 HomeController.php, PostController.php, UserController.php 생성
echo "<?php class HomeController { public function index() { echo 'Home Controller'; } }" > "$BASE_DIR/app/Controllers/HomeController.php"
echo "<?php class PostController { public function index() { echo 'Post Controller'; } }" > "$BASE_DIR/app/Controllers/PostController.php"
echo "<?php class UserController { public function index() { echo 'User Controller'; } }" > "$BASE_DIR/app/Controllers/UserController.php"

# Models에 HomeModel.php, PostModel.php, UserModel.php 생성
echo "<?php class HomeModel { public function getData() { return 'Home Model Data'; } }" > "$BASE_DIR/app/Models/HomeModel.php"
echo "<?php class PostModel { public function getData() { return 'Post Model Data'; } }" > "$BASE_DIR/app/Models/PostModel.php"
echo "<?php class UserModel { public function getData() { return 'User Model Data'; } }" > "$BASE_DIR/app/Models/UserModel.php"

# Core에 Router.php, Request.php, Response.php, Session.php, Validator.php, Database.php 생성
echo "<?php class Router { public function route() { echo 'Routing'; } }" > "$BASE_DIR/app/Core/Router.php"
echo "<?php class Request { public function get() { return 'Request Data'; } }" > "$BASE_DIR/app/Core/Request.php"
echo "<?php class Response { public function send() { echo 'Response Sent'; } }" > "$BASE_DIR/app/Core/Response.php"
echo "<?php class Session { public function start() { echo 'Session Started'; } }" > "$BASE_DIR/app/Core/Session.php"
echo "<?php class Validator { public function validate() { echo 'Validation'; } }" > "$BASE_DIR/app/Core/Validator.php"
echo "<?php class Database { public function connect() { echo 'Database Connected'; } }" > "$BASE_DIR/app/Core/Database.php"

# Services에 PostService.php, UserService.php 생성
echo "<?php class PostService { public function create() { echo 'Post Created'; } }" > "$BASE_DIR/app/Services/PostService.php"
echo "<?php class UserService { public function register() { echo 'User Registered'; } }" > "$BASE_DIR/app/Services/UserService.php"

# config 폴더 구조 생성
mkdir -p "$BASE_DIR/config"

# config 폴더에 config.php, database.php, routes.php, mail.php 생성
echo "<?php // Configuration File" > "$BASE_DIR/config/config.php"
echo "<?php // Database Configuration" > "$BASE_DIR/config/database.php"
echo "<?php // Routes Configuration" > "$BASE_DIR/config/routes.php"
echo "<?php // Mail Configuration" > "$BASE_DIR/config/mail.php"

# tests 폴더 생성
mkdir -p "$BASE_DIR/tests"

# tests에 PostTest.php, UserTest.php 생성
echo "<?php class PostTest { public function test() { echo 'Post Test'; } }" > "$BASE_DIR/tests/PostTest.php"
echo "<?php class UserTest { public function test() { echo 'User Test'; } }" > "$BASE_DIR/tests/UserTest.php"

echo "폴더와 파일이 성공적으로 생성되었습니다!"
