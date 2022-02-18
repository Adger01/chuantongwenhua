<?php

namespace Adger01\Chuantongwenhua;

trait Config
{
    public static $J2000 = 2451545;
    // 天干
    public static $gan = ["甲", "乙", "丙", "丁", "戊", "己", "庚", "辛", "壬", "癸"];
    // 地支
    public static $zhi = ["子", "丑", "寅", "卯", "辰", "巳", "午", "未", "申", "酉", "戌", "亥"];
    // 属相
    public static $shuxiang = ["鼠", "牛", "虎", "兔", "龙", "蛇", "马", "羊", "猴", "鸡", "狗", "猪"];
    // 星座
    public  static $xingzuo = ['摩羯', '水瓶', '双鱼', '白羊', '金牛', '双子', '巨蟹', '狮子', '处女', '天秤', '天蝎', '射手'];
    // 月相名称
    public static $yuexiangmingcheng = ["朔", "上弦", "望", "下弦"];
    // 24节气
    public static $jieqimingcheng = ['冬至', '小寒', '大寒', '立春', '雨水', '惊蛰', '春分', '清明', '谷雨', '立夏', '小满', '芒种', '夏至', '小暑', '大暑', '立秋', '处暑', '白露', '秋分', '寒露', '霜降', '立冬', '小雪', '大雪'];
    // 月份
    public static $yuemingcheng = ['十一', '十二', '正', '二', '三', '四', '五', '六', '七', '八', '九', '十']; // 月名称,建寅
    // 日期
    public static $rimingcheng = ['初一', '初二', '初三', '初四', '初五', '初六', '初七', '初八', '初九', '初十', '十一', '十二', '十三', '十四', '十五', '十六', '十七', '十八', '十九', '二十', '廿一', '廿二', '廿三', '廿四', '廿五', '廿六', '廿七', '廿八', '廿九', '三十', '卅一'];


    /**
     * 五行相生
     *
     * @var array
     */
    public static  $wuxingxiangsheng = [
        "金" => "水",
        "水" => "木",
        "木" => "火",
        "火" => "土",
        "土" => "金",
    ];

    /**
     * 五行相克，相泄
     *
     * @var array
     */
    public static $wuxingxiangke = [
        "金" => "木",
        "木" => "土",
        "土" => "水",
        "水" => "火",
        "火" => "金",
    ];
}