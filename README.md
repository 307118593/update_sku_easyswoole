# add_sku_sn

更新sku_sn字段  

1:EasySwooleEvent.php配置mysql连接  

2:php easyswoole start  

3:访问 127.0.0.1:9501/index/snow?page=1

每次5万条.
连接池配置:setMaxObjectNum(num) 最大池数量 = 逻辑核心数 * num

