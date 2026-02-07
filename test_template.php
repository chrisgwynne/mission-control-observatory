<?php
require 'kirby/bootstrap.php';

$kirby = new Kirby;
$site = $kirby->site();
$page = $site->find('agents');
echo "Page title: " . $page->title() . "\n";
echo "Template: " . $page->template() . "\n";
echo "Template file: " . $page->template()->file() . "\n";
echo "Intended template: " . $page->intendedTemplate() . "\n";
