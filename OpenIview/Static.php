<?php


class Openiview_Static  {

	public static function convertToUtf($strText){
		return iconv('ISO8859-8','UTF-8',hebrev(trim($strText)));
	}
}