<?php

/*
 * Faça o que quiser, beijo 'mim' liga.
 * REQUIRES LARAVELLLLLLLLLLLLLLLL
 */

/**
 * Valter Lorran's class virtual properties generator
 * WebSite: http:\\\\www.valterlorran.com
 * Facebook: fb.com/valter.lorran
 * @author valterlorran
 */
class VLGenerator {
    public static $c_start = "#@start@#";
    public static $c_end = "*endfile";
    public static function build(){
        //read the folder that contais our models. And bring all files as 
        //kyes and paths as values
        $array = self::listFolderFiles(app_path()."\models");
        foreach($array as $class_name=>$full_path){
            //easiest way to check if the class is abstract
            //so it probably is not a eloquet model
            $testClass = new ReflectionClass($class_name);
            if($testClass->isAbstract()){
                continue;
            }
            //Now we need to create an instance of our class by the text 
            $obj = new $class_name();
            //here I just check if the class is PDPEloquent
            //this class extends Eloquent, but has some functios that we use
            //here. I'll also add a OR to check if is Eloquent only.
            if(is_subclass_of($obj,"PDPEloquent") || is_subclass_of($obj,"Eloquent")){
                self::reWriteClass($obj, $class_name, $full_path);
            }
        }
    }
    
    public static function listFolderFiles($dir){
        $ffs = scandir($dir);
        $array = array();
        foreach($ffs as $ff){
            if($ff != '.' && $ff != '..'){
                if(is_dir($dir.'/'.$ff)) {
                    $array = array_merge(self::listFolderFiles($dir."\\".$ff), $array);
                }else{
                    $array[self::remDPHP($ff)] = $dir."\\".$ff;
                }
            }
        }
        return $array;
    }
    /**
     * Just removes the .php so we can get the class name with no trouble
     */
    public static function remDPHP($name){
        return str_replace(".php", "", $name);
    }
    /**
     * In this class I read and rewrite the php code. I just add some coments in
     * the files.
     * @param type $obj
     * @param type $class_name
     * @param type $full_path
     */
    public static function reWriteClass($obj, $class_name, $full_path){
        //Here I grab all the columns of the table
        $columns = DB::connection()->getDoctrineSchemaManager()->listTableColumns($obj->getTable());
        $properties = array();
        foreach($columns as $column) {
            $properties[] = "{$column->getType()->getName()} \${$column->getName()}";
        }
        //Now we open the file reading it.
        $fp = fopen($full_path, "r");
        //My stuff for you remember me forever <3
        $text = self::$c_start."\n\n/**\n";
        $text .= "* Valter Lorran's class virtual properties generator\n";
        $text .= "* WebSite: http:\\\\www.valterlorran.com\n";
        $text .= "* Facebook: fb.com/valter.lorran\n*\n";
        foreach($properties as $line){
            $text .= "* @property $line\n";
        }
        //I set the "class class_name" so we can replace it back and append the 
        //comments.
        $text .= self::$c_end."\n*/\n\n class {$class_name}";
        //Now we read the php text inside the file. Sure.
        $content = fread($fp, filesize($full_path));
        //replace it back
        $content = self::delete_all_between(self::$c_start, self::$c_end."\n*/\n\n", $content);
        $content = str_replace("class {$class_name}", $text, $content);
        fclose($fp);
        //lets write it
        $handle = fopen($full_path, 'w') or die('Cannot open file:  '.$my_file);
        fwrite($handle, $content);
        fclose($handle);
        echo "log:$class_name seems ok<br />";
    }
    
    public static function delete_all_between($beginning, $end, $string) {
        $beginningPos = strpos($string, $beginning);
        $endPos = strpos($string, $end);
        
        if ($beginningPos === false || $endPos === false) {
            return $string;
        }

        $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);

        return str_replace($textToDelete, '', $string);
    }

}
