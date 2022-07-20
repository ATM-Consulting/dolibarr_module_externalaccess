<?php
/* Copyright (C) 2004-2017	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2006		Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2007-2017	Regis Houssin			<regis.houssin@capnetworks.com>
 * Copyright (C) 2011		Philippe Grand			<philippe.grand@atoo-net.com>
 * Copyright (C) 2012		Juanjo Menent			<jmenent@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FI8TNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *		\file       htdocs/theme/eldy/style.css.php
 *		\brief      File for CSS style sheet Eldy
 */


if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', '1'); // Disable token renewal
if (!defined("NOCSRFCHECK")) define("NOCSRFCHECK", 1);

define('INC_FROM_CRON_SCRIPT',1);
define('ISLOADEDBYSTEELSHEET',1);


session_cache_limiter(FALSE);

require_once __DIR__ . '/../../config.default.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
dol_include_once('/externalaccess/class/color_tools.class.php');



// Load user to have $user->conf loaded (not done into main because of NOLOGIN constant defined)
if (empty($user->id) && ! empty($_SESSION['dol_login'])) $user->fetch('',$_SESSION['dol_login'],'',1);


// Define css type
header("Content-Type: text/css");
// Important: Following code is to avoid page request by browser and PHP CPU at each Dolibarr page access.
if (empty($dolibarr_nocache)) header('Cache-Control: max-age=3600, public, must-revalidate');
else header('Cache-Control: no-cache');

$primaryColor = !empty($conf->global->EACCESS_PRIMARY_COLOR)?$conf->global->EACCESS_PRIMARY_COLOR:'#F05F40';
$primaryColorHover = ColorTools::adjustBrightness($primaryColor,-30);
$headerImg = !empty($conf->global->EACCESS_HEADER_IMG)?$conf->global->EACCESS_HEADER_IMG:'../img/header_02.jpg';



?>

:root{
	--theme-primary-color : <?php print $primaryColor; ?>;
	--theme-primary-color-hover : <?php print $primaryColorHover; ?>;
}


body,
html {
  width: 100%;
  height: 100%;
}

body {
  font-family:  'Helvetica Neue', Arial, sans-serif;
  font-size: 0.9rem;
}

hr {
  max-width: 50px;
  border-width: 3px;
  border-color: var(--theme-primary-color);
}

hr.light {
  border-color: #fff;
}

a {
  color: var(--theme-primary-color);
  -webkit-transition: all 0.2s;
  -moz-transition: all 0.2s;
  transition: all 0.2s;
}

a:hover {
  color: var(--theme-primary-color);
}

h1,
h2,
h3,
h4,
h5,
h6 {
  font-family: 'Open Sans', 'Helvetica Neue', Arial, sans-serif;
}

.bg-primary {
  background-color: var(--theme-primary-color) !important;
}

.bg-dark {
  background-color: #212529 !important;
}

.text-faded {
  color: rgba(255, 255, 255, 0.7);
}

section {
  padding: 8rem 0;
}

section.type-content {
  padding: 4rem 0 8rem 0;
}

.iframe section {
    padding: 0.5rem 0 !important;
}


.section-heading {
  margin-top: 0;
}

::-moz-selection {
  color: #fff;
  background: #212529;
  text-shadow: none;
}

::selection {
  color: #fff;
  background: #212529;
  text-shadow: none;
}

img::selection {
  color: #fff;
  background: transparent;
}

img::-moz-selection {
  color: #fff;
  background: transparent;
}

#mainNav {
  border-bottom: 1px solid rgba(33, 37, 41, 0.1);
  background-color: #fff;
  font-family: 'Open Sans', 'Helvetica Neue', Arial, sans-serif;
  -webkit-transition: all 0.2s;
  -moz-transition: all 0.2s;
  transition: all 0.2s;
}

#mainNav .navbar-brand {
  font-weight: 700;
  text-transform: uppercase;
  color: var(--theme-primary-color);
  font-family: 'Open Sans', 'Helvetica Neue', Arial, sans-serif;
}

#mainNav .navbar-brand:focus, #mainNav .navbar-brand:hover {
  color: var(--theme-primary-color);
}

#mainNav .navbar-nav > li.nav-item > a.nav-link,
#mainNav .navbar-nav > li.nav-item > a.nav-link:focus {
  font-size: .9rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #212529;
}

#mainNav .navbar-nav > li.nav-item > a.nav-link:hover,
#mainNav .navbar-nav > li.nav-item > a.nav-link:focus:hover {
  color: var(--theme-primary-color);
}

#mainNav .navbar-nav > li.nav-item > a.nav-link.active,
#mainNav .navbar-nav > li.nav-item > a.nav-link:focus.active {
  color: var(--theme-primary-color) !important;
  background-color: transparent;
}

#mainNav .navbar-nav > li.nav-item > a.nav-link.active:hover,
#mainNav .navbar-nav > li.nav-item > a.nav-link:focus.active:hover {
  background-color: transparent;
}

.navbar .logo{
	max-height: 30px;
}

#logo{
	display: none;
}

.login-logo-container img{
	max-width: 100%;
}

@media (min-width: 992px) {
	#logo{
		display: inline;
	}
	#logoshrink{
		display: none;
	}
	#mainNav.navbar-shrink #logoshrink{
		display: inline;
	}
	#mainNav.navbar-shrink #logo{
		display: none;
  	}

  #mainNav {
    border-color: transparent;
    background-color: transparent;
  }
  #mainNav .navbar-brand {
    color: rgba(255, 255, 255, 0.7);
  }
  #mainNav .navbar-brand:focus, #mainNav .navbar-brand:hover {
    color: #fff;
  }
  #mainNav .navbar-nav > li.nav-item > a.nav-link {
    padding: 0.5rem 1rem;
  }
  #mainNav .navbar-nav > li.nav-item > a.nav-link,
  #mainNav .navbar-nav > li.nav-item > a.nav-link:focus {
    color: rgba(255, 255, 255, 0.8);
  }
  #mainNav .navbar-nav > li.nav-item > a.nav-link:hover,
  #mainNav .navbar-nav > li.nav-item > a.nav-link:focus:hover {
    color: #fff;
  }


  #mainNav.navbar-light {

    border-bottom: 0px solid rgba(0, 0, 0, 0);
	background: -moz-linear-gradient(top, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0) 100%); /* FF3.6-15 */
	background: -webkit-linear-gradient(top, rgba(0,0,0,0.65) 0%,rgba(0,0,0,0) 100%); /* Chrome10-25,Safari5.1-6 */
	background: linear-gradient(to bottom, rgba(0,0,0,0.65) 0%,rgba(0,0,0,0) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */

  }

  #mainNav.navbar-shrink {
    border-bottom: 1px solid rgba(33, 37, 41, 0.1);
    background: #fff;
  }
  #mainNav.navbar-shrink .navbar-brand {
    color: var(--theme-primary-color);
  }
  #mainNav.navbar-shrink .navbar-brand:focus, #mainNav.navbar-shrink .navbar-brand:hover {
    color: var(--theme-primary-color);
  }
  #mainNav.navbar-shrink .navbar-nav > li.nav-item > a.nav-link,
  #mainNav.navbar-shrink .navbar-nav > li.nav-item > a.nav-link:focus {
    color: #212529;
  }
  #mainNav.navbar-shrink .navbar-nav > li.nav-item > a.nav-link:hover,
  #mainNav.navbar-shrink .navbar-nav > li.nav-item > a.nav-link:focus:hover {
    color: var(--theme-primary-color);
  }
}


header.commonhead {
  padding-top: 5rem;
  padding-bottom: calc(5rem - 56px);
  min-height: 100px;
  background-image: url("<?php print $headerImg; ?>");
  background-position: center center;
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
}

header.masthead {
  padding-top: 10rem;
  padding-bottom: calc(10rem - 56px);
  background-image: url("<?php print $headerImg; ?>");
  background-position: center center;
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
}

header.masthead hr {
  margin-top: 30px;
  margin-bottom: 30px;
}

header.masthead h1 {
  font-size: 2rem;
}

header.masthead p {
  font-weight: 300;
}

@media (min-width: 768px) {
  header.masthead p {
    font-size: 1.15rem;
  }
}

@media (min-width: 992px) {
  header.masthead {
    height: 100vh;
    min-height: 650px;
    padding-top: 0;
    padding-bottom: 0;
  }
  header.masthead h1 {
    font-size: 3rem;
  }
}

@media (min-width: 1200px) {
  header.masthead h1 {
    font-size: 4rem;
  }
}

.service-box {
  max-width: 400px;
}

.service-box a{
    text-decoration: none;
}

.service-box-disabled{
    cursor: not-allowed;
}

.portfolio-box {
  position: relative;
  display: block;
  max-width: 650px;
  margin: 0 auto;
}

.portfolio-box .portfolio-box-caption {
  position: absolute;
  bottom: 0;
  display: block;
  width: 100%;
  height: 100%;
  text-align: center;
  opacity: 0;
  color: #fff;
  background: rgba(240, 95, 64, 0.9);
  -webkit-transition: all 0.2s;
  -moz-transition: all 0.2s;
  transition: all 0.2s;
}

.portfolio-box .portfolio-box-caption .portfolio-box-caption-content {
  position: absolute;
  top: 50%;
  width: 100%;
  transform: translateY(-50%);
  text-align: center;
}

.portfolio-box .portfolio-box-caption .portfolio-box-caption-content .project-category,
.portfolio-box .portfolio-box-caption .portfolio-box-caption-content .project-name {
  padding: 0 15px;
  font-family: 'Open Sans', 'Helvetica Neue', Arial, sans-serif;
}

.portfolio-box .portfolio-box-caption .portfolio-box-caption-content .project-category {
  font-size: 14px;
  font-weight: 600;
  text-transform: uppercase;
}

.portfolio-box .portfolio-box-caption .portfolio-box-caption-content .project-name {
  font-size: 18px;
}

.portfolio-box:hover .portfolio-box-caption {
  opacity: 1;
}

.portfolio-box:focus {
  outline: none;
}

@media (min-width: 768px) {
  .portfolio-box .portfolio-box-caption .portfolio-box-caption-content .project-category {
    font-size: 16px;
  }
  .portfolio-box .portfolio-box-caption .portfolio-box-caption-content .project-name {
    font-size: 22px;
  }
}

.text-primary {
  color: var(--theme-primary-color) !important;
}

.btn {
  border: none;
  border-radius: 300px;
  font-family: 'Open Sans', 'Helvetica Neue', Arial, sans-serif;
}

.btn-strong{
	font-weight: 700;
	text-transform: uppercase;
}

.btn-xl {
  padding: 1rem 2rem;
}

.btn-primary {
  background-color: var(--theme-primary-color);
  border-color: var(--theme-primary-color);
}

.btn-primary:hover, .btn-primary:focus, .btn-primary:active {
  color: #fff;
  background-color: var(--theme-primary-color-hover) !important;
}

.btn-primary:active, .btn-primary:focus {
  box-shadow: 0 0 0 0.2rem rgba(240, 95, 64, 0.5) !important;
}


/* Login panel*/
/*
 * Card component
 */
.card {
    background-color: #ffffff;
    /* just in case there no content*/
    padding: 20px 25px 30px;
    margin: 0 auto 25px;
    margin-top: 50px;
    /* shadows and rounded borders */
    -moz-border-radius: 2px;
    -webkit-border-radius: 2px;
    border-radius: 2px;
    -moz-box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
    -webkit-box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
    box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
}

.profile-img-card {
    width: 96px;
    height: 96px;
    margin: 0 auto 10px;
    display: block;
    -moz-border-radius: 50%;
    -webkit-border-radius: 50%;
    border-radius: 50%;
}

/*
 * Form styles
 */
.profile-name-card {
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    margin: 10px 0 0;
    min-height: 1em;
}

.reauth-email {
    display: block;
    color: #404040;
    line-height: 2;
    margin-bottom: 10px;
    font-size: 14px;
    text-align: center;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
}

.form-signin #inputEmail,
.form-signin #inputPassword {
    direction: ltr;
    height: 44px;
    font-size: 16px;
}

.form-signin input[type=email],
.form-signin input[type=password],
.form-signin input[type=text],
.form-signin button {
    width: 100%;
    display: block;
    margin-bottom: 10px;
    z-index: 1;
    position: relative;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
}

.form-signin .form-control:focus {
    border-color: rgb(104, 145, 162);
    outline: 0;
    -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgb(104, 145, 162);
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgb(104, 145, 162);
}

.btn.btn-signin {
    /*background-color: #4d90fe; */
    background-color: var(--theme-primary-color);;
    /* background-color: linear-gradient(rgb(104, 145, 162), rgb(12, 97, 33));*/
    padding: 0px;
    font-weight: 700;
    font-size: 14px;
    height: 36px;
    -moz-border-radius: 3px;
    -webkit-border-radius: 3px;
    border-radius: 3px;
    border: none;
    -o-transition: all 0.218s;
    -moz-transition: all 0.218s;
    -webkit-transition: all 0.218s;
    transition: all 0.218s;
}

.btn.btn-signin:hover,
.btn.btn-signin:active,
.btn.btn-signin:focus {
    background-color: var(--theme-primary-color);;
}

.forgot-password {
    color: rgb(104, 145, 162);
}

.forgot-password:hover,
.forgot-password:active,
.forgot-password:focus{
    color: rgb(12, 97, 33);
}

.margin-top-if-not-empty:not(:empty){
	margin-top: 20px;
}


.list-group {
	margin-bottom: 20px;
	padding-left: 0
}

.list-group-item {
	position: relative;
	display: block;
	padding: 10px 15px;
	margin-bottom: -1px;
	background-color: #fff;
	border: 1px solid #ddd
}

.list-group-item:first-child {
	border-top-right-radius: 0;
	border-top-left-radius: 0
}

.list-group-item:last-child {
	margin-bottom: 0;
	border-bottom-right-radius: 0;
	border-bottom-left-radius: 0
}

.list-group-item>.badge {
	float: right
}

.list-group-item>.badge+.badge {
	margin-right: 5px
}

.list-group-item.active,
.list-group-item.active:hover,
.list-group-item.active:focus {
	z-index: 2;
	color: #fff;
	background-color: var(--theme-primary-color);
	border-color: var(--theme-primary-color)
}

.list-group-item.active .list-group-item-heading,
.list-group-item.active:hover .list-group-item-heading,
.list-group-item.active:focus .list-group-item-heading {
	color: inherit
}

.list-group-item.active .list-group-item-text,
.list-group-item.active:hover .list-group-item-text,
.list-group-item.active:focus .list-group-item-text {
	color: #f9bab7
}

a.list-group-item {
	color: #555
}

a.list-group-item .list-group-item-heading {
	color: #333
}

a.list-group-item:hover,
a.list-group-item:focus {
	text-decoration: none;
	background-color: #f5f5f5
}

.list-group-item-heading {
	margin-top: 0;
	margin-bottom: 5px
}

.list-group-item-text {
	margin-bottom: 0;
	line-height: 1.3
}

.panel {
	margin-bottom: 18px;
	background-color: #fff;
	border: 1px solid transparent;
	border-radius: 0;
	-webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
	box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05)
}

.panel-body {
	padding: 15px
}

.panel-body:before,
.panel-body:after {
	content: " ";
	display: table
}

.panel-body:after {
	clear: both
}

.panel>.list-group {
	margin-bottom: 0
}

.panel>.list-group .list-group-item {
	border-width: 1px 0
}

.panel>.list-group .list-group-item:first-child {
	border-top-right-radius: 0;
	border-top-left-radius: 0
}

.panel>.list-group .list-group-item:last-child {
	border-bottom: 0
}

.panel-heading+.list-group .list-group-item:first-child {
	border-top-width: 0
}

.panel>.table {
	margin-bottom: 0
}

.panel>.panel-body+.table {
	border-top: 1px solid #d6d4d4
}

.panel-heading {
	padding: 10px 15px;
	border-bottom: 1px solid transparent;
	border-top-right-radius: 1px;
	border-top-left-radius: 1px;
}

.panel-title {
	margin-top: 0;
	margin-bottom: 0;
	font-size: 15px
}

.panel-title>a {
	color: inherit
}

.panel-footer {
	padding: 10px 15px;
	background-color: #f5f5f5;
	border-top: 1px solid #ddd;
	border-bottom-right-radius: 1px;
	border-bottom-left-radius: 1px
}

.panel-group .panel {
	margin-bottom: 0;
	border-radius: 0;
	overflow: hidden
}

.panel-group .panel+.panel {
	margin-top: 5px
}

.panel-group .panel-heading {
	border-bottom: 0
}

.panel-group .panel-heading+.panel-collapse .panel-body {
	border-top: 1px solid #ddd
}

.panel-group .panel-footer {
	border-top: 0
}

.panel-group .panel-footer+.panel-collapse .panel-body {
	border-bottom: 1px solid #ddd
}

.panel-default {
	border-color: #ddd
}

.panel-default>.panel-heading {
	color: #333;
	background-color: #f5f5f5;
	border-color: #ddd
}

.panel-default>.panel-heading+.panel-collapse .panel-body {
	border-top-color: #ddd
}

.panel-default>.panel-footer+.panel-collapse .panel-body {
	border-bottom-color: #ddd
}

.panel-primary {
	border-color: var(--theme-primary-color)
}

.panel-primary>.panel-heading {
	color: #fff;
	background-color: var(--theme-primary-color);
	border-color: var(--theme-primary-color)
}

.panel-primary>.panel-heading+.panel-collapse .panel-body {
	border-top-color: var(--theme-primary-color)
}

.panel-primary>.panel-footer+.panel-collapse .panel-body {
	border-bottom-color: var(--theme-primary-color)
}

.panel-success {
	border-color: #48b151
}

.panel-success>.panel-heading {
	color: #fff;
	background-color: #55c65e;
	border-color: #48b151
}

.panel-success>.panel-heading+.panel-collapse .panel-body {
	border-top-color: #48b151
}

.panel-success>.panel-footer+.panel-collapse .panel-body {
	border-bottom-color: #48b151
}

.panel-warning {
	border-color: #e4752b
}

.panel-warning>.panel-heading {
	color: #fff;
	background-color: #fe9126;
	border-color: #e4752b
}

.panel-warning>.panel-heading+.panel-collapse .panel-body {
	border-top-color: #e4752b
}

.panel-warning>.panel-footer+.panel-collapse .panel-body {
	border-bottom-color: #e4752b
}

.panel-danger {
	border-color: #d4323d
}

.panel-danger>.panel-heading {
	color: #fff;
	background-color: #f3515c;
	border-color: #d4323d
}

.panel-danger>.panel-heading+.panel-collapse .panel-body {
	border-top-color: #d4323d
}

.panel-danger>.panel-footer+.panel-collapse .panel-body {
	border-bottom-color: #d4323d
}

.panel-info {
	border-color: #4b80c3
}

.panel-info>.panel-heading {
	color: #fff;
	background-color: #5192f3;
	border-color: #4b80c3
}

.panel-info>.panel-heading+.panel-collapse .panel-body {
	border-top-color: #4b80c3
}

.panel-info>.panel-footer+.panel-collapse .panel-body {
	border-bottom-color: #4b80c3
}




.btn.btn-xs{
	padding: 0.2em 0.75em;
	font-size:0.8em;
}

.btn-top-section{
      margin-top: -80px;
}


body.iframe .container {
    max-width: none !important;
}


/* OR separator : <div class="or"> OR </div> */
.or {
    display:flex;
    justify-content:center;
    align-items: center;
    color:grey;
}

.or:after,
.or:before {
    content: "";
    display: block;
    border-top: 1px solid #b3c1ce;
    border-bottom: 1px solid #b3c1ce;
    width: 30%;
    height:3px;
    margin: 0 10px;
}

.show-invalid:invalid {
	background-color: pink;
	border-color: darkred;
}


/* MENU PATCH */
.dropdown-submenu {
	position: relative;
}

.dropdown-submenu a::after {
	transform: rotate(-90deg);
	position: absolute;
	right: 6px;
	top: .8em;
}

.dropdown-submenu .dropdown-menu {
	top: 0;
	left: 100%;
	margin-left: .1rem;
	margin-right: .1rem;
}

.ticket-help-msg-wrap {
	margin-bottom: 30px;
}



<?php

include_once __DIR__ . '/timeline.inc.php';
include_once __DIR__ . '/status.inc.php';


