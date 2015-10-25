# wordpress-api

wordpress的api接口

## 发现的bug

今天刚发现一个bug，如果一篇文章同时属于两个分类，则【获取文章列表】接口中，文章会出现两次。还未解决。

## 技术支持

[http://www.brighttj.com/wordpress/wordpress-api.html](http://www.brighttj.com/wordpress/wordpress-api.html)

## 接口文档

[http://tangjr.gitbooks.io/brighttj-api/content/index.html](http://tangjr.gitbooks.io/brighttj-api/content/index.html)

## 注意事项

<font color=red>因为本站用了WP-PostViews插件（用来统计文章浏览量的，很好用），所以可以通过wp_postmeta表中的字段来获得文章浏览量，如果没有安装这个插件，那么需要删除SQL语句中与views相关的语句，否则会报错。</font>
