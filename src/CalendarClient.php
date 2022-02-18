<?php

namespace Adger01\Chuantongwenhua;

use Overtrue\ChineseCalendar\Calendar;

class CalendarClient
{
    use Config;

    /**
     * 获取基本信息
     *
     * @param [type] $year
     * @param [type] $month
     * @param [type] $day
     * @param [type] $hour
     * @return void
     */
    public function solar($year, $month, $day, $hour = null)
    {
        $calendar = new Calendar();
        $this->solar = $calendar->solar($year, $month, $day, $hour);

        // 格式化数据
        $this->formatSolar();

        // 计算五行系数
        $this->wuxingScore();

        // 计算喜用神
        $this->xiyongshen();


        return $this->solar;
    }



    /**
     * 格式化数据
     *
     * @return void
     */
    private function formatSolar()
    {
        $this->solar["gan_year"] = mb_substr($this->solar["ganzhi_year"], 0, 1);
        $this->solar["zhi_year"] = mb_substr($this->solar["ganzhi_year"], 1, 1);
        $this->solar["gan_month"] = mb_substr($this->solar["ganzhi_month"], 0, 1);
        $this->solar["zhi_month"] = mb_substr($this->solar["ganzhi_month"], 1, 1);
        $this->solar["gan_day"] = mb_substr($this->solar["ganzhi_day"], 0, 1);
        $this->solar["zhi_day"] = mb_substr($this->solar["ganzhi_day"], 1, 1);
        $this->solar["gan_hour"] = mb_substr($this->solar["ganzhi_hour"], 0, 1);
        $this->solar["zhi_hour"] = mb_substr($this->solar["ganzhi_hour"], 1, 1);


        $this->solar["wuxing"]["金"] = 0;
        $this->solar["wuxing"]["木"] = 0;
        $this->solar["wuxing"]["水"] = 0;
        $this->solar["wuxing"]["火"] = 0;
        $this->solar["wuxing"]["土"] = 0;
    }

    public function wuxingScore()
    {
        // 取出4个天干，取出月支
        $zhi_month = $this->solar["zhi_month"];
        $bazi_4gan = $this->getBazi4Gan();
        $wuxings = ["金" => 0, "木" => 0, "水" => 0, "火" => 0, "土" => 0];
        // 计算天干的五行系数
        foreach ($bazi_4gan as $gan) {
            $wuxing = Tiangan::getWuxingByGan($gan);
            $wuxings[$wuxing] += Tiangan::getWeightByTianGanAndMonth($gan, $zhi_month);
        }

        // 取出地支 ，取出月支
        $bazi_4zhi = $this->getBazi4Zhi();
        $zhi_month = $this->solar["zhi_month"];
        // 计算地支的五行系数
        foreach ($bazi_4zhi as $zhi) {
            $wuxing = DiZhi::getWuxingByZhi($zhi);
            $cangGan = DiZhi::getWeightByZhiAndMonth($zhi, $zhi_month);
            foreach ($cangGan as $gan => $weight) {
                $wuxing = Tiangan::getWuxingByGan($gan);
                $wuxings[$wuxing] += $weight;
            }
        }

        $this->solar["wuxing"] =  $wuxings;
    }

    /**
     * 取出四柱的4个天干，也就是八字中的4个天干
     */
    private function getBazi4Gan()
    {
        return [$this->solar["gan_year"], $this->solar["gan_month"], $this->solar["gan_day"], $this->solar["gan_hour"]];
    }

    /**
     * 取出四柱的4个地支，也就是八字中的4个支
     */
    private function getBazi4Zhi()
    {
        return [$this->solar["zhi_year"], $this->solar["zhi_month"], $this->solar["zhi_day"], $this->solar["zhi_hour"]];
    }

    private function xiyongshen()
    {
        // 取日干数据
        $gan_day = $this->solar["gan_day"];
        $wuxing = $this->solar["wuxing"];

        // 取日干的五行
        $gan_day_wuxing = TianGan::getWuxingByGan($gan_day);

        // 取出同类五行
        $wuxingTonglei = $this->tongleiwuxing($gan_day_wuxing);

        // 异类五行
        $wuxingYilei = $this->yileiwuxing($gan_day_wuxing);

        // 五行总分值
        $wuxingZongFenzhi = array_sum($wuxing);

        $wuxingTongleiFenzhi = $wuxingYileiFenzhi = $rizhuFenzhi = 0;

        // 同类分值
        foreach ($wuxingTonglei as $tong) {
            $wuxingTongleiFenzhi += $wuxing[$tong];
        }

        // 异类分值
        foreach ($wuxingYilei as $yi) {
            $wuxingYileiFenzhi += $wuxing[$yi];
        }

        // 日主的分值
        $rizhuFenzhi = $wuxing[$gan_day_wuxing];

        // 同类五行的强度
        $riganWuxingQiangdu = $rizhuFenzhi / $wuxingZongFenzhi;
        // 异类五行的强度
        $wuxingTongleiQiangdu = $wuxingTongleiFenzhi / $wuxingZongFenzhi;

        // 属性相克 
        $ke = $this->getwuxing_ke($gan_day_wuxing);
        $sheng = $this->shengwodewuxing($gan_day_wuxing);
        $xie = $this->xiewodewuxing($gan_day_wuxing);
        $diwuxing = $this->diwuxing($gan_day_wuxing);

        // 代表未处理
        $flag = 0;
        $this->solar["xiyongshen"]["name"] = $gan_day_wuxing;
        $this->solar["xiyongshen"]["type"] = "";
        $this->solar["xiyongshen"]["desc"] = "";
        $this->solar["xiyongshen"]["gan_day_wuxing"] = $gan_day_wuxing;

        /**
         * 偏弱:
         * 偏弱的标准：从数量上看，同种五行1到3个；五行强度不高于全局的20%。
         * 对于偏弱格局，生和帮是首选。
         * 举例说明：戊申　乙卯　辛未　壬辰，此格局日主辛金偏弱，可生，土生金，也可帮扶，金帮金。
         */
        if ($wuxingTongleiFenzhi >= 1 && $wuxingTongleiFenzhi <= 3 && $wuxingTongleiQiangdu < 0.2) {
            $flag = 1;
            $this->solar["xiyongshen"]["name"] = $sheng;
            $this->solar["xiyongshen"]["type"] = "偏弱";
            $this->solar["xiyongshen"]["desc"] = "偏弱格局，生和帮是首选。";
            $this->solar["xiyongshen"]["gan_day_wuxing"] = $gan_day_wuxing;
        }

        /**
         * 旺极：
         * 旺极的标准为：１．同类五行6至8个；2.五行强度不低于全局五行强度的80%。
         * 对于旺极格局，要注意旺极必衰，所以应以生为主，根据情况可考虑泄和帮。
         * 举例说明，己丑　戊辰　己巳　戊辰，这个格局土旺极，补火为最佳，土、金也可。
         */
        if ($wuxingTongleiFenzhi >= 6 && $wuxingTongleiFenzhi <= 8 && $wuxingTongleiQiangdu < 0.8) {
            $flag = 1;
            $this->solar["xiyongshen"]["name"] = $sheng;
            $this->solar["xiyongshen"]["type"] = "旺极";
            $this->solar["xiyongshen"]["desc"] = "旺极必衰，所以应以生为主，根据情况可考虑泄和帮。";
            $this->solar["xiyongshen"]["gan_day_wuxing"] = $gan_day_wuxing;
        }

        /**
         * 偏旺
         * 即日主五行强度在总强度的20％—50%之间，同种五行数量为2—4个
         */
        if ($wuxingTongleiFenzhi >= 2 && $wuxingTongleiFenzhi <= 4 && $wuxingTongleiQiangdu > 0.2 && $wuxingTongleiQiangdu < 0.5) {
            $flag = 1;
            $this->solar["xiyongshen"]["name"] = $ke;
            $this->solar["xiyongshen"]["type"] = "偏旺";
            $this->solar["xiyongshen"]["desc"] = "偏旺宜克";
            $this->solar["xiyongshen"]["gan_day_wuxing"] = $gan_day_wuxing;
        }

        /**
         * 太旺，太旺的标准为：1.同类五行4至6个；2.要占全局强度的50%至80%；3.五行格局中只有1到3个克、泄、耗的五行。
         * 五行相克，我们上文中说过了，比如水克火，火就是耗水的五行，那么水生木，木就是泄水的五行，土克水，那么土就是克水的五行。
         * 如果是太旺的格局，那么泄就是首选，比如：甲申　壬申　庚申　辛巳，这个就是太旺格局，需泄，也就是需要补水。
         */
        if ($wuxingTongleiFenzhi >= 4 && $wuxingTongleiFenzhi <= 6 && $wuxingTongleiQiangdu > 0.5 && $wuxingTongleiQiangdu < 0.8) {
            $flag = 1;
            $this->solar["xiyongshen"]["name"] = $xie;
            $this->solar["xiyongshen"]["type"] = "太旺";
            $this->solar["xiyongshen"]["desc"] = "太旺的格局，那么泄就是首选";
            $this->solar["xiyongshen"]["gan_day_wuxing"] = $gan_day_wuxing;

            //那么泄就是首选 
        }

        if ($flag == 0) {
            $this->solar["xiyongshen"]["name"] = $diwuxing;
            $this->solar["xiyongshen"]["type"] = "中和";
            $this->solar["xiyongshen"]["desc"] = "中和状态";
            $this->solar["xiyongshen"]["gan_day_wuxing"] = $gan_day_wuxing;
        }
    }

    /**
     * 传入五行，返回生我的五行
     */
    private function shengwodewuxing($wuxing)
    {
        $wuxingxiangshengFlip = array_flip(self::$wuxingxiangsheng);
        return $wuxingxiangshengFlip[$wuxing];
    }

    /**
     * 传入五行，返回同类五行
     */
    private function tongleiwuxing($wuxing)
    {
        $tongleiwuxing = $this->shengwodewuxing($wuxing);
        return [$wuxing, $tongleiwuxing];
    }

    /**
     * 传入五行，返回异类五行
     */
    private function yileiwuxing($wuxing)
    {
        $allWuxing = array_values(self::$wuxingxiangsheng);
        $tongleiwuxing = $this->tongleiwuxing($wuxing);
        return array_values(array_diff($allWuxing, $tongleiwuxing));
    }

    /**
     * 传入五行，返回相克
     */
    private function getwuxing_ke($wuxing)
    {
        $shuikewoFlip = array_flip(self::$wuxingxiangke);
        return $shuikewoFlip[$wuxing];
    }

    /**
     * 传入五行，返回相泄
     */
    private function xiewodewuxing($wuxing)
    {
        $wuxings = self::$wuxingxiangsheng;
        return $wuxings[$wuxing];
    }

    /**
     * 传入五行，返回最低的五行
     * zhongwuxing
     */
    private function diwuxing($wuxing)
    {
        $wuxing = $this->solar["wuxing"];
        asort($wuxing);
        return array_keys($wuxing)[0];
    }
}
