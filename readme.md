# 说明：
1. 本项目是基于php7.0.27调试的，故只保证在php7.0.27项目可跑
2. 发布metadata为了速度是基于异步请求，故拿到dna未必真实发布成功。可能会有几秒延迟
3. 本项目没有去处理类里面的异常情况，没有添加请求的超时时间，这些请使用者自行更改。

# 扩展：
1. 需要安装扩展
  - https://github.com/web3p/secp256k1

# 运行：
1. 进入项目根目录
2. 执行composer install，安装依赖
3. php demo.php，至此就结束了，最后会输出一个dna，可以直接去链上查询（查询api参考官方提供文档）


[详细文档](http://yuanbenlian.mydoc.io/)