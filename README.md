# Smart.Framework
(c) 2009 - 2016 unix-world.org
License: BSD

Smart.Framework - A High Performance PHP / Javascript Framework for Web Projects
* Clean Code
* Full Decoupled Libraries
* Independent Packages (based on plugins and dependency-injection)
* Great Architecture (a hybrid between Multi-Tier and Middleware, combined with clean code separation as MVC)

#### Easy coding != Performance
The primary goal of Smart.Framework is to provide a very fast and responsive web framework.
Thus, Smart.Framework provides the best balance between acceptable coding and performance.

So, it is a very light and fast PHP / Javascript web framework.
The paradox is that it have more built-in features than the well-known frameworks like Zend Framework 2 or Symfony 2
but is 2x ... 9x faster in the terms of requests / second, so it can serve many million users per day.

#### Demo:
http://demo.unix-world.org/smart-framework/

#### Download Stable Releases:
http://sourceforge.net/projects/warp-cms/files/smart-framework/

#### Benchmark scenario:
Using a the same basic page for all tested frameworks, just a simple text: "Benchmark Test".
Benchmark was running with Apache Benchmark suite with the command:
ab -n 1200 -c 150 http://{localhost}/{framework}/{web-root}
Using a regular server as hardware platform:
* 2 x CPU AMD Opteron 2384 @ 2.7 GHz quad core 64-bit (Total: 8 cores / 8 threads)
* 24 GB RAM, DDR2 @ 667 ECC
* HDD 2 x 1TB Hitachi 7200rpm
Software:
* OS: Linux, kernel 3.x
* Apps: Apache 2.4.17, PHP 5.5.30 with Zend Opcache 7.0.5

#### Benchmark Results of PHP Frameworks:
* Smart.Framework r.160221: ~ 2677 requests / second
* Zend Framework 2.4.7: ~ 391 requests / second ( 9x slower than Smart.Framework )
* Symfony 2.7.3: ~ 799 requests / second ( 7x slower than Smart.Framework )
* Silex 1.2.4: ~ 1981 requests / second ( 2x slower than Smart.Framework )
* Laravel 5.0 ~ 568 requests / second ( 5x slower than Smart.Framework )
