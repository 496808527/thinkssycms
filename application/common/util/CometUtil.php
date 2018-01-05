<?php

namespace app\common\util;

class CometUtil {

  public static function buildno() {
    return "YT" . base_convert(uniqid(), 10, 36);
  }

  public static function Simple_export($header, &$data, $filename) {
    set_time_limit(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename=' . $filename);
    header('Cache-Control: max-age=0');
    $fp = fopen('php://output', 'a');
    if (!empty($header)) {
      foreach ($header as $key => $value) {
        $value = iconv("utf-8", "gbk//TRANSLIT", $value);
        $header[$key] = $value;
      }
      fputcsv($fp, $header);
      unset($header);
    }

    $count = 230;
    $temp = 0;
    foreach ($data as $key => $value) {
      $value = array_filter($value);
      $temp++;
      if ($temp >= $count) {
        $temp = 0;
        ob_flush();
        flush();
      }
      foreach ($value as $key => $item) {
        $item = iconv("utf-8", "gbk//TRANSLIT", $item);
        $value[$key] = $item;
      }
      fputcsv($fp, $value);
      unset($data[$key]);
    }
    unset($data);
    fclose($fp);
    exit();
  }

  public static function CsvFileRead($filename) {
    if (file_exists($filename)) {
      $file = fopen($filename, "r");
      $rows = [];
      while (!feof($file)) {
        $temp = fgets($file);
        $temp = stringutil::str_gbk_to_utf8($temp);
        $temp = Arrayutil::stringToArray($temp, ",");
        $rows[] = $temp;
      }
      fclose($file);
      return $rows;
    } else {
      return [];
    }
  }

  public static function export($header, $data, $filename) {
    set_time_limit(0);
    $sqlCount = count($data);
    $sqlLimit = 10000; //每次只从数据库取100000条以防变量缓存太大
    // 每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
    $limit = 10000;
    // buffer计数器
    $cnt = 0;
    $fileNameArr = array();
    // 逐行取出数据，不浪费内存
    $filename = stringutil::str_utf8_to_gbk($filename);
    for ($i = 0; $i < ceil($sqlCount / $sqlLimit); $i++) {
      $fp = fopen($filename . "$i" . ".csv", 'w'); //生成临时文件
      $fileNameArr[] = $filename . "$i" . ".csv";
      // 将数据通过fputcsv写到文件句柄
      fputcsv($fp, $header);
      $temp = Arrayutil::sub_array($data, $i * $sqlLimit, $sqlLimit);
      foreach ($temp as $a) {
        $cnt++;
        if ($limit == $cnt) {
          //刷新一下输出buffer，防止由于数据过多造成问题
          ob_flush();
          flush();
          $cnt = 0;
        }
        fputcsv($fp, $a);
      }
      fclose($fp);  //每生成一个文件关闭
    }
    unset($data);
    return $fileNameArr;
  }

  public static function trimparms($parms) {
    if (empty($parms)) {
      return [];
    } else {
      foreach ($parms as $key => $value) {
        if (is_array($value)) {
          $parms[$key] = $value;
        } else {
          $parms[$key] = trim($value);
        }
      }
      return $parms;
    }
  }

  public static function ExcelReader($filename) {
    vendor("PHPExcel.PHPExcel.PHPExcel");
    vendor("PHPExcel.PHPExcel.IOFactory");
    if(empty($filename)){
      return ["state"=>"err","msg"=>"文件名不能为空"];
    }
    $filename = ROOT_PATH . 'public'.DS .$filename;
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if ($ext === "xlsx" || $ext === "xls") {
      $reader = \PHPExcel_IOFactory::createReader("Excel2007");
    } else if ($ext === "csv") {
      $reader = \PHPExcel_IOFactory::createReader("CSV");
    } else {
      return ["state" => "err", "msg" => "非法的文件格式"];
    }

    $excelinfo = $reader->load($filename);
    $sheetinfo = $excelinfo->getActiveSheet();
    $cels = $sheetinfo->getHighestColumn(); //总列数
    $rows = $sheetinfo->getHighestRow(); //总行数

    $data = [];
    for ($i = 1; $i <= $rows; $i++) {
      $item = [];
      for ($column = 'A'; $column <= $cels; $column++) {
        $value = $sheetinfo->getCell($column . $i)->getValue();
        if ($sheetinfo->getCell($column . $i)->getDataType() == \PHPExcel_Cell_DataType::TYPE_NUMERIC) {
          $cellstyleformat = $sheetinfo->getCell($column . $i)->getFormattedValue();
          if (strlen($value)) {
            if (preg_match('/\d{2}\/\d{2}\/\d{2}\s\d{1,2}:\d{1,2}$/', $cellstyleformat)) {
              $item[] = gmdate("Y-m-d H:i:s", \PHPExcel_Shared_Date::ExcelToPHP($value));
            } else {
              if($column=="G"){
                $item[] = gmdate("Y-m-d H:i:s", \PHPExcel_Shared_Date::ExcelToPHP($value));
              }else{
                $item[] = trim($value);
              }
              
            }
          }
        } else {
          if (strlen($value)) {
            $item[] = trim($value);
          }
        }
      }
      $data[] = $item;
      unset($item);
    }
    return $data;
  }

  public static function excelArrayincex($ceils) {
    $arrayCeil = [1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F', 7 => 'G', 8 => 'H', 9 => 'I', 10 => 'J', 11 => 'K', 12 => 'L',
        13 => 'M', 14 => 'N', 15 => 'O', 16 => 'P', 17 => 'Q', 18 => 'R', 19 => 'S', 20 => 'T', 21 => 'U', 22 => 'V', 23 => 'W', 24 => 'X',
        25 => 'Y', 26 => 'Z'];
    $data = [];
    if ($ceils <= 26) {
      $ceilsindex = $ceils % 26;
      for ($j = 1; $j <= $ceilsindex; $j++) {
        $data[$j] = $arrayCeil[$j];
      }
    } else {
      $rows = intval($ceils / 26);
      $ceilsindex = $ceils % 26;

      for ($i = 1; $i <= $rows; $i++) {
        if ($i === $rows) {
          for ($j = 1; $j <= $ceilsindex; $j++) {
            $index = $i * 26 + $j;
            $data[$index] = $arrayCeil[$i] . $arrayCeil[$j];
          }
        } else {
          for ($j = 1; $j <= 26; $j++) {
            $index = $i * 26 + $j;
            $data[$index] = $arrayCeil[$i] . $arrayCeil[$j];
          }
        }
      }
    }
    return $data;
  }

}
