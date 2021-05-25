# 简介
## 细节就是效率；效率就是金钱【该项目适用于初创型项目以及初中级phper】
#### `hyperfly`旨在为中小企业或者初创型公司提供一套可以快速开发项目，加快项目尽快落地，以节约在项目初期的各种成本投入，以及提供对初中级程序员更加友好的开发工具
#### `hyperfly`是基于`Swoole`框架`hyperf`的基础上，开发的一套纯接口化的后台RBAC权限管理系统。提供了基本的RBAC权限控制，以及纯接口化开发的规范
## 开发hyperfly的初衷
#### **自**从12年开始学习web开发以来，从12年那会接触到的smarty模板引擎到现在各种yii2，laravel等各种框架，都是需要后端程序员开发前端代码，到现在依然存在很多需要前端写好模板给后端开发套用的。尤其在15年之前，在开发后端管理系统的时候，几乎都是后端开发兼自己写管理系统的页面，在我开发过的后台中，html/css/js的代码开发占据几乎50%以上的工作内容。虽然现在的主流框架都有提供纯API式的开发，但是提供的也仅仅是基本的功能，没有一些更加高效低成本的方式
#### **对**于开发人员来说。很多前端人员其实是很抗拒去接触后端代码，尤其是现在前端开发已经形成了一个系统性工程，不再像以前看起来那么屌丝了。而后端人员，虽然不是很抗拒写前端代码，但是更多的是想把有限的精力花在后端上面。尤其是现在`Swoole`的出现，给php开发注入了一股新的力量，phper们也想更好的学习后端开发
#### **对**于公司而言。尤其是初创公司，php带来的好处是无疑的。低成本高效率迭代容易。几乎所有的项目都需要开发后端管理系统以及三端的API接口，在现在物联网趋势下，甚至以后需要开发更多端的接口。根据我做过项目的经验，个人认为纯API的对接在效率上会优于传统的混合开发
#### **但**是现在在市面上找不到一个纯接口化的基于`Swoole`的RBAC框架。这也是我开发`hyperfly`框架的初衷。想开发一个更加容易上手，减少学习成本的，对初中级程序员更加友好，又能满足中小型项目的RBAC后台管理框架
## 项目截图
![image](http://yangjianyong.cn/wp-content/uploads/2021/05/hyperfly01.png)
![image](http://yangjianyong.cn/wp-content/uploads/2021/05/hyperfly02.png)
![image](http://yangjianyong.cn/wp-content/uploads/2021/05/hyperfly03.png)
![image](http://yangjianyong.cn/wp-content/uploads/2021/05/hyperfly04.png)
![image](http://yangjianyong.cn/wp-content/uploads/2021/05/hyperfly05.png)
![image](http://yangjianyong.cn/wp-content/uploads/2021/05/hyperfly06.png)
![image](http://yangjianyong.cn/wp-content/uploads/2021/05/hyperfly07.png)
## 功能介绍
### `hyperfly`
#### 项目地址 : [https://github.com/vankour/hyperfly](https://github.com/vankour/hyperfly "https://github.com/vankour/hyperfly")
#### `hyperfly`提供了经典的`RBAC`权限控制功能、并且基于现在纯API开发的趋势下，采用了接口即权限的方式，权限控制的实现全部是纯接口化的
#### `RBAC`的实现。传统的`RBAC`要么逻辑设计上不够清晰、要么过于复杂，对于初中级开发者理解难度过大，对于初创型项目不够合适。`hyperfly`实现了更加清晰简单的逻辑。即管理员、菜单、角色、权限、接口各自独立不耦合。基于`hyperf`的注解功能，一键生成系统中所有注解到路由的接口到数据库，并且采用接口即权限的方式，将权限分配给角色，再将角色分配给管理员，把菜单从权限中解耦出来，可以独立的将菜单分配给管理员。思路清晰，并且满足项目初期的需求
### `HyperflyAdmin`
#### 项目地址 : [https://github.com/vankour/HyperflyAdmin](https://github.com/vankour/HyperflyAdmin "https://github.com/vankour/HyperflyAdmin")
#### `HyperflyAdmin`，简称`HA`。`HA`采用了`bootstrap`后台框架`AdminLTE`，，集成了`AdminLTE`提供的整套`web`开发的`jquery`组件，采用`jQuery`以及`pjax`单页面技术。弹窗采用的国产的`layer`
#### **目前只是用爱发电，只提供了基于传统的`jQuery`实现的后台页面。后续有需要实现`vue`版本**
#### 各类组件版本
**`FontAwesome-Free-5.15.3`**
**`AdminLTE-3.1.0`**
**`jQuery-3.6.0`**
**`bootstrap-5.0.0-beta3`**
**`layer-3.3.0`**

## 使用
### `hyperfly`安装。通过`composer`安装
```shell
composer create-project vankour/hyperfly

php bin/hyperf.php start
```
### `HyperflyAdmin`安装
```shell
git clone https://github.com/vankour/HyperflyAdmin.git
```
