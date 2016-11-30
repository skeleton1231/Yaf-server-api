<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午1:30
 */
class ThemelistModel extends PdoDb
{

    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_themelist';
    }

    public function saveProperties()
    {
        $this->properties['user_id'] = $this->user_id;
        $this->properties['theme']   = $this->theme;
        $this->properties['created'] = $this->created;

    }

    public function getTheme($theme_id=1){
        $sql='
        SELECT 
        id,theme,title,post_file
        FROM
        `bibi_themelist`
        WHERE
        id ='.$theme_id.'
        ';

        $theme=$this->query($sql);
        if($theme){
           $info=@$theme[0];
           $info["post_file"]="http://img.bibicar.cn/".$info['post_file'];
        }else{
          $info=array();
        }
        
       
        return $info;
    }



    public function getThemes($type=1,$userId=1,$page=1){
      
       $pageSize = 10;
       $sql='
       SELECT 
       id,post_file,user_id,theme,title,created,sort,is_skip
       FROM `bibi_themelist`
       WHERE is_hot=1 AND type='.$type.'
       ORDER BY sort DESC
          ';

       $number = ($page-1)*$pageSize;
       $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';

       $sqlNearByCnt = '
            SELECT
            COUNT(id) AS total
            FROM
            `bibi_themelist` 
            WHERE is_hot=1 AND type='.$type.'
            ';
       
       $total = $this->query($sqlNearByCnt)[0]['total'];
       $theme = $this->query($sql);
       
       foreach($theme as $key =>$value){
               $theme[$key]["post_file"]="http://img.bibicar.cn/".$value['post_file'];
       }

       $count = count($theme);
       $list['theme_list']=array();
       $list['theme_list'] = $theme;
       $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
       $list['total'] = $total;
       return $list;
    }


    public function deleteTheme($ThemeId){

        $sql = ' DELETE FROM `bibi_themelist` WHERE `id` = '.$ThemeId.' AND `user_id` = '.$this->currentUser.' ';

        $this->execute($sql);

    }





}