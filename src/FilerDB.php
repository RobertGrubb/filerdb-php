<?php

foreach (glob(dirname(__FILE__) . '/FilerDB/**/*.php') as $filename) {
  require_once $filename;
}
