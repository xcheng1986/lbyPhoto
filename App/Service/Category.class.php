<?php

namespace App\Service;

/**
 * Description of CategoryService
 *
 * @author Administrator
 */
class Category
{

    public static $model;

    public function __construct()
    {
        if (is_null(self::$model)) {
            self::$model = db();
        }
    }

    public function table_category()
    {
        return 'categories';
    }

    public function info($category_id)
    {
        $model = db();
        return $model->find('select * from ' . $this->table_category() . ' where id= ' . $category_id . ' limit 1') ?: [];
    }

    /**
     * 获取相册列表
     * @return type
     */
    public function lists($order_by = 'id desc', $is_public = null)
    {
        return self::$model->select('select * from ' . $this->table_category() . (!is_null($is_public) ? (' where is_public="' . $is_public . '" ') : '') . ' order by ' . $order_by . ' desc') ?: [];
    }

    /**
     * 获取所有的分类信息
     * @return type
     */
    public function all()
    {
        $model = db();
        $all = $model->select('SELECT * FROM ' . $this->table_category() . ' WHERE 1');
        return array_column($all, null, 'id');
    }

    /**
     * 添加相册
     * @param type $name
     * @param type $comment
     * @param type $cover_image_id
     * @param type $dir
     * @param type $rank
     * @param type $is_public
     * @param type $commentable
     * @param type $image_order
     * @param type $permalink
     */
    public function add($name, $comment = '', $cover_image_id = 0, $dir = '', $rank = 100, $is_public = 1, $commentable = 1, $image_order = 1, $permalink = '')
    {
        $sql = 'insert into ' . $this->table_category() . ' (`name`,`comment`,`cover_image_id`,`dir`,`rank`,`is_public`,`commentable`,`image_order`,`permalink`,`create_time`) values (';
        $sql .= '"' . addslashes($name) . '","' . addslashes($comment) . '",' . (int) $cover_image_id . ',"' . addslashes($dir) . '",' . $rank . ',"' . $is_public . '","' . $commentable . '","';
        $sql .= $this->getOrderString($image_order) . '","' . $permalink . '","' . date('Y-m-d H:i:s') . '")';
        return self::$model->update($sql) ? true : false;
    }

    /**
     * update
     * @param type $category_id
     * @param type $name
     * @param type $comment
     * @param type $rank
     * @param type $is_public
     * @param type $commentable
     * @param type $image_order
     * @param type $permalink
     * @return type
     */
    public function update($category_id, $name, $comment, $rank, $is_public, $commentable, $image_order, $permalink)
    {
        $sql = 'update ' . $this->table_category() . ' set name="' . addslashes($name) . '",comment="' . addslashes($comment) . '",rank=' . $rank . ',is_public="' . $is_public . '",commentable="' . $commentable . '",image_order=' . $image_order . ',permalink="' . $permalink . '",update_time="' . date('Y-m-d H:i:s') . '" where id=' . $category_id . ' limit 1';
        return self::$model->update($sql);
    }

    public function getOrderString($number)
    {
        switch ($number) {
            case 1:
                return 'create_time desc';
            case 2:
                return 'create_time asc';
            case 3:
                return 'file_name desc';
            case 4:
                return 'file_name asc';
            case 5:
                return 'score desc';
            case 6:
                return 'score asc';
            default :
                return 'create_time desc';
        }
    }

    public function update_category_last_upload_time($category_id)
    {
        return self::$model->update('update ' . $this->table_category() . ' set last_upload_time="' . date('Y-m-d H:i:s') . '" where id= ' . $category_id . ' limit 1') ?: false;
    }

    /**
     * is_category_name_used
     * @param type $name
     * @return type
     */
    public function is_category_name_used($name, $category_id = null)
    {
        return self::$model->find('select id from ' . $this->table_category() . ' where `name`="' . addslashes($name) . '"' . ($category_id ? (' and id<>' . $category_id) : '') . ' limit 1') ?: [];
    }

    /**
     * is_dir_used
     * @param type $dir
     * @return type
     */
    public function is_dir_used($dir, $category_id = null)
    {
        return self::$model->find('select id from ' . $this->table_category() . ' where `dir`="' . addslashes($dir) . '"' . ($category_id ? (' and id<>' . $category_id) : '') . ' limit 1') ?: [];
    }

    /**
     * is_permalink_used
     * @param type $permalink
     * @return type
     */
    public function is_permalink_used($permalink, $category_id = null)
    {
        return self::$model->find('select id from ' . $this->table_category() . ' where `permalink`="' . addslashes($permalink) . '"' . ($category_id ? (' and id<>' . $category_id) : '') . ' limit 1') ?: [];
    }

}
