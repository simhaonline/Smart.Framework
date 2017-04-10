# Smart.Framework, a PHP / Javascript Web Framework
(c) 2009 - 2017 unix-world.org
License: BSD

Smart.Framework - A High Performance PHP / Javascript Framework for Web Projects (websites and web applications)
* Clean Code
* Full Decoupled Libraries
* Independent Packages (based on plugins and dependency-injection)
* Great Architecture (a hybrid between Multi-Tier and Middleware, combined with clean code separation as MVC)

#### Easy coding != Performance
The primary goal of Smart.Framework is to provide a very fast and responsive web framework.
Thus, Smart.Framework provides the best balance between acceptable coding and performance.

So, it is a very light, fast and reach featured PHP / Javascript web framework.
The ultra high level software architecture of this web framework allows it to deliver a paradox:
* it have more default built-in features than the well-known frameworks compared with Zend Framework, Symfony or Laravel
* it delivers much more performance being between 3x to 9x faster (as requests / second) compared with Zend Framework, Symfony or Laravel
Thus this is a web framework that can really serve many million users per day with a single server !
When used with SQL / Non-SQL Databases and combined with In-Memory DB like Redis or Memcache it beats Varnish in many aspects:
* delivers ~ the same speed as Varnish but allows granulary level caching policy of zones in controllers
* works also with HTTPS (Varnish does not)
* caching policies can be controlled to expire based on content / GET or POST variables INPUT even with changing COOKIES

#### Demo:
http://demo.unix-world.org/smart-framework/

#### Download Stable Releases:
http://sourceforge.net/projects/warp-cms/files/smart-framework/

#### Benchmark scenario:
Using a the same basic page for all tested frameworks, just a simple text: "Benchmark Test".
Benchmark was running with Apache Benchmark suite with the command:
ab -n 1500 -c 150 http://{localhost}/{framework}/{web-root}
Using a regular server as hardware platform (Supermicro):
* 2 x Intel(R) Xeon(R) CPU E5-2650 v4 @ 2.20GHz 64-bit (Total: 24 cores / 48 threads)
* 128 GB RAM, DDR4 ECC @ 2133 MHz
* HDD 2 x 1TB SSD
Software:
* OS: Debian 8 Linux, 64-bit
* Apps: Apache 2.4.23, PHP 5.6.29 (with Zend Opcache 7.0.6)

#### Benchmark Results of PHP Frameworks (with a simple controller that Outputs: "Hello World" ; no Memcache or Redis):
* Smart.Framework v.3.1.1 (2017.04.10): ~ 21.5K (21501) requests / second
* Silex 2.0.4: ~ 7.2K (7229) requests / second ( 3x slower than Smart.Framework )
* Symfony 3.2.2: ~ 3.3K (3322) requests / second ( 6x slower than Smart.Framework )
* Laravel 5.4.3 ~ 3.0K (3017) requests / second ( 7x slower than Smart.Framework )
* Zend Framework 2.4.11: ~ 2.5K (2569) requests / second ( 9x slower than Smart.Framework )
