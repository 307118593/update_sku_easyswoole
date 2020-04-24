<?php


namespace App\HttpController;


use App\Models\Item;
use App\Pool\StdPool;
use co;
use EasySwoole\Http\AbstractInterface\Controller;
use App\Models\B1Item;
use EasySwoole\ORM\DbManager;
use EasySwoole\Pool\Manager;
use EasySwoole\Utility\Random;
use EasySwoole\Utility\SnowFlake;
use EasySwoole\Pool\MagicPool;
use EasySwoole\Component\AtomicManager;

require './vendor/autoload.php';

class Index extends Controller
{

    function index()
    {
        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/welcome.html';
        if (!is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/welcome.html';
        }
        $this->response()->write(file_get_contents($file));
    }

    protected function actionNotFound(?string $action)
    {
        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if (!is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }


    /**
     * Notes:
     * User: Song
     * Date: 2020/4/5
     * @return bool
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function snow()
    {

        ini_set('memory_limit', '1024M');
        $limit = 50000;
        $request = $this->request();
        $page = $request->getRequestParam('page');
//        return $this->writeJson($page);
        $ids = B1Item::create()->limit($limit * ($page - 1), $limit)->order('id','ASC')->column('id');
//        return $this->writeJson(200,$ids);
        AtomicManager::getInstance()->add('second', 0);

        foreach ($ids as $v) {
            go(function () use ($v) {
                $str = SnowFlake::make(1, 1);
                DbManager::getInstance()->invoke(function ($client) use ($v, $str) {
                    //返回$client连接对象
                    $B1Item = B1Item::invoke($client);
                    $res = $B1Item->update(['sku_sn' => "YJX".$str], ['id' => $v]);
                    $atomic = AtomicManager::getInstance()->get('second');
                    $atomic->add(1);
                    if ($res != 1 ){
                        echo "失败的id:".$v."\n";
                    }
                    if($atomic->get() % 500 == 0){
                        //输出执行数量
                        echo $atomic->get() . ',';
                    }
                    return true;
                }, 'default', 10000.0);
                return true;

            });

        }
        return $this->writeJson(200,'OK');


    }


    /**
     * Notes:查看b1item和item的外键是否是一对一
     * Date: 2020/4/9
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getB1ToB3Id()
    {
        ini_set('memory_limit', '1024M');
        $ids = B1Item::create()->column('id');
        foreach ($ids as $v) {
            go(function () use ($v,$client) {
                DbManager::getInstance()->invoke(function ($client) use ($v) {
                    $B3Item = Item::invoke($client);
                    $count = $B3Item->where('top_id', $v)->count();
//                    if ($count > 1) {
                        echo $v.',';
//                    }
                    return true;
                }, 'default', 1000.0);
                return true;
            });
//            co::sleep(0.001);
        }
        return $this->writeJson('okk');
    }


    public function calc(){
        $time = microtime(true);
        $sum = 0;
        for ($i=0;$i<=120000000;$i++){
            $sum += $i;
        }

        echo "\n";
        echo "\n";
        echo "\n";
        echo "从1累加到12亿的结果:".$sum."  \n";
        echo "\n";
        echo ("PHP执行耗时(s):".(microtime(true)-$time));
        echo "\n";
        echo "\n";
        echo "\n";
        echo "\n";
        return $this->writeJson(200,'okk');
    }

}