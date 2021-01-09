<?php if (! defined('ISLOADEDBYSTEELSHEET')) die('Must be call by steelsheet'); ?>
/* <style type="text/css" > */
/* Start timeline.inc.php */

/*
 *	TIME LINE
 */
.main-timeline{
	overflow: hidden;
	position: relative;
	padding: 60px 0;
}
.main-timeline:before{
	content: "";
	width: 1px;
	height: 100%;
	background: #cfcdcd;
	position: absolute;
	top: 0;
	left: 50%;
}
.main-timeline .timeline{
	width: 50%;
	clear: both;
	position: relative;
}
.main-timeline .timeline:before,
.main-timeline .timeline:after{
	content: "";
	display: block;
	clear: both;
}
.main-timeline .timeline:first-child:before,
.main-timeline .timeline:last-child:before{
	content: "";
	width: 11px;
	height: 11px;
	background: #cfcdcd;
	box-sizing: content-box;
	border: 5px solid #fff;
	box-shadow: 0 0 0 2px #cfcdcd;
	position: absolute;
	top: -54px;
	right: -11px;
	transform: rotate(45deg);
}
.main-timeline .timeline:last-child:before{
	top: auto;
	bottom: -54px;
}
.main-timeline .timeline:last-child:nth-child(even):before{
	right: auto;
	left: -11px;
}
.main-timeline .timeline-icon{
	width: 24px;
	height: 24px;
	background: #fff;
	border: 1px solid #cfcdcd;
	position: absolute;
	top: 17px;
	right: -13px;
	z-index: 1;
	transform: rotate(45deg);
}
.main-timeline .timeline-icon:before{
	content: "";
	display: block;
	width: 15px;
	height: 15px;
	background: #fff;
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	transition: background-color 0.2s ease 0s;
}
.main-timeline .timeline:hover .timeline-icon:before{ background: #39b3ff; }
.main-timeline .timeline-content{
	width: 85%;
	padding: 18px 30px;
	background: #fff;
	text-align: right;
	float: left;
	border: 1px solid transparent;
	position: relative;
	transition: all 0.3s ease 0s;
}
.main-timeline .timeline:hover .timeline-content{
	border: 1px solid #cfcdcd;
}
.main-timeline .timeline-content:before{
	content: "";
	display: block;
	width: 14px;
	height: 14px;
	background: #fff;
	border: 1px solid #cfcdcd;
	position: absolute;
	top: 21px;
	right: -7.3px;
	transform: rotate(45deg);
	transition: all 0.2s ease 0s;
}
.main-timeline .timeline:hover .timeline-content:before{
	background: #39b3ff;
	border-color: #39b3ff;
}
.main-timeline .timeline-content:after{
	content: "";
	width: 11%;
	height: 1px;
	background: #cfcdcd;
	position: absolute;
	top: 28px;
	right: -14%;
}
.main-timeline .date{
	display: block;
	font-size: 15px;
	font-weight: 600;
	color: #39b3ff;
	margin: 0 0 8px;
	transition: all 0.3s ease 0s;
}
.main-timeline .timeline:hover .date{ color: #444; }
.main-timeline .title{
	font-size: 18px;
	color: #444;
	margin-top: 0;
	transition: all 0.3s ease 0s;
}
.main-timeline .timeline:hover .title{ color: #39b3ff; }
.main-timeline .description{
	font-size: 16px;
	color: #777;
	line-height: 28px;
	margin-top: 8px;
}
.main-timeline .timeline:nth-child(2n),
.main-timeline .timeline:nth-child(2n) .timeline-content{
	float: right;
	text-align: left;
}
.main-timeline .timeline:nth-child(2n) .timeline-icon{
	right: 0;
	left: -12px;
}
.main-timeline .timeline:nth-child(2n) .timeline-content:before{ left: -7.3px; }
.main-timeline .timeline:nth-child(2n) .timeline-content:after{ left: -14%; }


.main-timeline.main-timeline-right{ padding-left: 20px; }
.main-timeline.main-timeline-right:before{ left: 20px; }
.main-timeline.main-timeline-right .timeline{ width: 100%; }
.main-timeline.main-timeline-right .timeline,
.main-timeline.main-timeline-right .timeline-content{
	float: right;
	text-align: left;
}
.main-timeline.main-timeline-right .timeline:first-child:before,
.main-timeline.main-timeline-right .timeline:last-child:nth-child(odd):before{
	right: auto;
	left: -11px;
}
.main-timeline.main-timeline-right .timeline-icon{
	right: 0;
	left: -12px;
}
.main-timeline.main-timeline-right .timeline-content:before{ left: -7.3px; }
.main-timeline.main-timeline-right .timeline-content:after{ left: -14%; }


@media only screen and (max-width: 767px){
	.main-timeline{ padding-left: 20px; }
	.main-timeline:before{ left: 20px; }
	.main-timeline .timeline{ width: 100%; }
	.main-timeline .timeline,
	.main-timeline .timeline-content{
		float: right;
		text-align: left;
	}
	.main-timeline .timeline:first-child:before,
	.main-timeline .timeline:last-child:nth-child(odd):before{
		right: auto;
		left: -11px;
	}
	.main-timeline .timeline-icon{
		right: 0;
		left: -12px;
	}
	.main-timeline .timeline-content:before{ left: -7.3px; }
	.main-timeline .timeline-content:after{ left: -14%; }
}

/*
* Component: Timeline
* -------------------
*/
.timeline {
	position: relative;
	margin: 0 0 30px 0;
	padding: 0;
	list-style: none;
}
.timeline:before {
	content: '';
	position: absolute;
	top: 0;
	bottom: 0;
	width: 4px;
	background: #ddd;
	left: 31px;
	margin: 0;
	border-radius: 2px;
}
.timeline > li {
	position: relative;
	margin-right: 0;
	margin-bottom: 15px;
}
.timeline > li:before,
.timeline > li:after {
	content: " ";
	display: table;
}
.timeline > li:after {
	clear: both;
}
.timeline > li > .timeline-item {
	-webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	box-shadow:  0 1px 3px rgba(0, 0, 0, 0.1);
	border:1px solid #d2d2d2;
	border-radius: 3px;
	margin-top: 0;
	background: #fff;
	color: #444;
	margin-left: 60px;
	margin-right: 0px;
	padding: 0;
	position: relative;
}

.timeline > li.timeline-code-ticket_msg_private  > .timeline-item {
	background: #fffbe5;
	border-color: #d0cfc0;
}


.timeline > li > .timeline-item > .time{
	color: #6f6f6f;
	float: right;
	padding: 10px;
	font-size: 12px;
}


.timeline > li > .timeline-item > .timeline-header-action{
	color: #6f6f6f;
	float: right;
	padding: 7px;
	font-size: 12px;
}


a.timeline-btn:link,
a.timeline-btn:visited,
a.timeline-btn:hover,
a.timeline-btn:active
{
	display: inline-block;
	margin-bottom: 0;
	font-weight: 400;
	border-radius: 0;
	box-shadow: none;
	padding: 1px 5px;
	font-size: 12px;
	line-height: 1.5;
	text-align: center;
	white-space: nowrap;
	vertical-align: middle;
	touch-action: manipulation;
	cursor: pointer;
	user-select: none;
	background-image: none;
	text-decoration: none;
	background-color: #f4f4f4;
	color: #444;
	border: 1px solid #ddd;
}

a.timeline-btn:hover
{
	background-color: #e7e7e7;
	color: #333;
	border-color: #adadad;;
}


.timeline > li > .timeline-item > .timeline-header {
	margin: 0;
	color: #333;
	border-bottom: 1px solid #f4f4f4;
	padding: 10px;
	font-size: 14px;
	font-weight: normal;
	line-height: 1.1;
}

.timeline > li > .timeline-item > .timeline-footer {
	border-top: 1px solid #f4f4f4;
}

.timeline > li.timeline-code-ticket_msg_private  > .timeline-item > .timeline-header, .timeline > li.timeline-code-ticket_msg_private  > .timeline-item > .timeline-footer {
	border-color: #ecebda;
}

.timeline > li > .timeline-item > .timeline-header > a {
	font-weight: 600;
}
.timeline > li > .timeline-item > .timeline-body,
.timeline > li > .timeline-item > .timeline-footer {
	padding: 10px;
}
.timeline > li > .fa,
.timeline > li > .glyphicon,
.timeline > li > .ion {
	width: 30px;
	height: 30px;
	font-size: 15px;
	line-height: 30px;
	position: absolute;
	color: #666;
	background: #d2d6de;
	border-radius: 50%;
	text-align: center;
	left: 18px;
	top: 0;
}
.timeline > .time-label > span {
	font-weight: 600;
	padding: 5px;
	display: inline-block;
	background-color: #fff;
	border-radius: 4px;
}
.timeline-inverse > li > .timeline-item {
	background: #f0f0f0;
	border: 1px solid #ddd;
	-webkit-box-shadow: none;
	box-shadow: none;
}
.timeline-inverse > li > .timeline-item > .timeline-header {
	border-bottom-color: #ddd;
}

.timeline-icon-todo,
.timeline-icon-in-progress,
.timeline-icon-done{
	color: #fff !important;
}

.timeline-icon-not-applicble{
	color: #000;
	background-color: #f7f7f7;
}

.timeline-icon-todo{
	background-color: #dd4b39 !important;
}

.timeline-icon-in-progress{
	background-color: #00c0ef !important;
}
.timeline-icon-done{
	background-color: #00a65a !important;
}


.timeline-badge-date{
	background-color: #0073b7 !important;
	color: #fff !important;
}

.timeline-documents-container{

}

.timeline-documents{
	margin-right: 5px;
}

img.userphoto {			/* size for user photo in lists */
	border-radius: 0.72em;
	width: 1.4em;
	height: 1.4em;
	background-size: contain;
	vertical-align: middle;
}



/* End timeline.inc.php */
