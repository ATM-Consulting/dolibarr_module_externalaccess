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





/* End timeline.inc.php */
