# Smart.Framework, a modern, high performance PHP / Javascript Framework for Web featuring MVC + Middlewares
## License: BSD
(c) 2009 - 2018 unix-world.org
<b>For support or questions: <i>support @ unix-world.org</i></b>
<br>
#### Demo URL:
<a href="http://demo.unix-world.org/smart-framework/">http://demo.unix-world.org/smart-framework/</a>
#### Download URL:
<a href="https://github.com/unix-world/Smart.Framework" target="_blank">https://github.com/unix-world/Smart.Framework</a>
#### Download URL for Modules:
<a href="https://github.com/unix-world/Smart.Framework.Modules" target="_blank">https://github.com/unix-world/Smart.Framework.Modules</a>
<br>

### Smart.Framework design philosophy:
* Web oriented approach: to offer a platform for building websites or web based applications for Web Clients, Desktops and Mobiles
* Native Cloud Server Services (built-in) for: WebDAV Server, CalDAV Server, CardDAV Server
* Native Cloud Client Provider (built-in) for HTTP/HTTPS access which supports the full range of HTTP(S) Methods / Requests: GET, POST, PUT, DELETE, ...
* Clean Code: MVC code pattern with built-in Dependency-Injection
* Hybrid Architecture: combined Multi-Tier and Middlewares architectures to provide a flexible and smart service
* Independent Packages: based on modules, plugins and 3rd party (vendor) libraries that can be integrated or developed
* Full Decoupled Core Libraries: the framework core is using independent (decoupled) libraries (no 3rd party dependencies)
* NameSpace Separation: for modules (Models, Views, Controllers) or Libraries
* Based on a previous experience of more than 17 years of developing web projects, research and experiments using web technologies

#### Easy Coding != Performance
The primary goal of Smart.Framework is to provide a very fast and responsive web framework.
Thus, Smart.Framework provides the optimal balance between acceptable coding skills and performance delivered.

It is very light, fast, reach featured PHP / Javascript web framework.
The original software architecture of this web framework allows it to deliver a paradox:
* it have more default built-in features than the well-known frameworks compared with Zend Framework, Symfony or Laravel
* it delivers much more performance being between 3x to 9x faster (as requests / second) compared with Zend Framework, Symfony or Laravel
Thus this is a web framework that can really serve many million users per day with a single server !
When used with SQL / Non-SQL Databases and combined with In-Memory DB like Redis or Memcache it beats Varnish in many aspects:
* delivers ~ the same speed as Varnish but allows granulary level caching policy of zones in controllers
* works also with HTTPS (by example, Varnish does not)
* caching policies can be controlled to expire based on content / GET or POST variables INPUT even with changing COOKIES
This framework is compatible, stable and actively tested with PHP 5.6 / 7.0 / 7.1 / 7.2 / 7.3 versions.
<br>

### Benchmark Scenario:
Using a the same basic page for all tested frameworks, just a simple text: 'Benchmark Test'.
Benchmark was running with Apache Benchmark suite with the command:
ab -n 2500 -c 150 http://{localhost}/{framework}/{benchmark-page}
Using a regular server as hardware platform (Supermicro):
* 2 x Intel(R) Xeon(R) CPU E5-2650 v4 @ 2.20GHz 64-bit (Total: 24 cores / 48 threads)
* 128 GB RAM, DDR4 ECC @ 2133 MHz
* HDD 2 x 1TB SSD
Software:
* OS: Debian 8 Linux, 64-bit, up-to-date 2017-09-05
* Apps: Apache 2.4.23, PHP 5.6.30 (with Zend Opcache 7.0.6)

#### Benchmark Results of tested PHP Frameworks (with a simple controller that Outputs: 'Hello World' ; no Memcache or Redis):
* Smart.Framework v.3.5.7 (2017.09.27): ~ 21.7K (21721) requests / second
* Silex 2.0.4: ~ 7.2K (7229) requests / second ( 3x slower than Smart.Framework )
* Symfony 3.3.8: ~ 3.3K (3374) requests / second ( 6x slower than Smart.Framework )
* Laravel 5.4.3 ~ 3.0K (3015) requests / second ( 7x slower than Smart.Framework )
* Zend Framework 2.4.11: ~ 2.5K (2568) requests / second ( 9x slower than Smart.Framework )
