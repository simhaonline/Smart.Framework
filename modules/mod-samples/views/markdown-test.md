# [####TITLE|html####] (H1)

&copy;&nbsp;&nbsp;2015-2019&nbsp;unix-world.org # v.20190105

## H2 (Unicode Test) ăĂîÎâÂşŞţŢ

### H3 (Alternate Unicode Test) ăĂîÎâÂșȘțȚ

#### H4

##### H5

###### H6

Alternatively, for H1 and H2, an underline-ish style:

Alt-H1
======

Alt-H2
------

Line 1
Line2
```space```

[Go To Anchor](#anchor)

Emphasis, aka italics, with *asterisks* or _underscores_.

PHP code (PHP Start-Tag and PHP End-Tag must be removed ...)
```php
<?php

// php sample code
function test() {
	echo 'this is a test ...';
}

test();

?>
```

##### Test PHP code injection:
<?
test();
?>

```space```

Strong emphasis, aka bold, with **asterisks** or __underscores__.

Combined emphasis with **asterisks and _underscores_**.

<a href="#">Strikethrough</a> uses two tildes. ~~Scratch this.~~

Subscript is just ~this~ ...
Superscript is ^this^ ...
Syntax:
CO~2~ for subscript
E=mc^2^ for superscript

|{@class=hidden}|
| :---: |
| some centered text |

|{@class=hidden}|
| ---: |
| some text aligned on right |

<div style="height:100px; background:#DDEEFF;">abcd</div>

1. First ordered list item
2. Another item
	* Unordered sub-list.
1. Actual numbers don't matter, just that it's a number
	1. Ordered sub-list
4. And another item.

		You can have properly indented paragraphs within list items. Notice the blank line above, and the leading spaces (at least one, but we'll use three here to also align the raw Markdown).

		To have a line break without a paragraph, you will need to use two trailing spaces.⋅⋅

        Note that this line is separate, but within the same paragraph.
		(This is contrary to the typical GFM line break behaviour, where trailing spaces are not required.)

* Unordered list can use asterisks
- Or minuses
+ Or pluses

[Link with attributes link](http://netbsd.org) {.primary9 #link .Upper-Case @data-smart=open,modal$700$300}

my email is <me@example.com>

[I'm an inline-style link](https://www.google.com)

[I'm an inline-style link with title](https://www.google.com "Google's Homepage")

[I'm a reference-style link][Arbitrary case-insensitive reference text]

[I'm a relative reference to a repository file](lib/license_bsd.txt)

[You can use numbers for reference-style link definitions][1]

Or leave it empty and use the [link text itself]

Some text to show that the reference links can follow later.

[arbitrary case-insensitive reference text]: https://www.mozilla.org
[1]: http://slashdot.org
[link text itself]: http://www.reddit.com

Here's our logo (hover to see the title text):

Inline-style:
![alt text](https://github.com/adam-p/markdown-here/raw/master/src/common/images/icon48.png "Logo Title Text 1") {@width=100 @style=box-shadow:$10px$10px$5px$#888888;}

Reference-style:
![alt text][logo]

[logo]: https://github.com/adam-p/markdown-here/raw/master/src/common/images/icon48.png "Logo Title Text 2"

Inline `code` has `back-ticks around` it.

```javascript
// javascript sample code
var s = "JavaScript syntax highlighting";
alert(s);
```

```python
# python sample code
s = "Python syntax highlighting"
print s
```

```html
<!-- HTML sample code -->
<img src="some-image.svg">
```

```plaintext
This is a
plain text
with no highlight and some <tag>Tag</tag> ...
```

```
No language indicated, so no syntax highlighting (fallback to PlainText).
But let's throw in a <b>tag</b>.
```

| One {@class=bordered}     | Two {@class=bordered}        | Three {@class=bordered}   | Four {@class=bordered}         |
| ------------- |-------------| ---------| ------------- |
| One {@class=bordered}     | Two {@class=bordered}        | Three {@class=bordered}   | Four {@class=bordered}         |

| One     | Two        | Three   | Four          |
| ------------- |-------------| ---------| ------------- |
| Span Across |||a {@colspan=3}|

|          Grouping {@colspan=3 @class=bordered}            |  First Header {@class=bordered}  | Second Header {@class=bordered} | Third Header {@class=bordered} |
 ------------ | :-----------: | :-----------: | :---------: | :---------: | ---------:
Content {@rowspan=2 @class=bordered}  | *Long Cell* {@colspan=5 @class=bordered} ||
**Cell** {@colspan=3 @class=bordered} |  Cell {@colspan=2 @class=bordered}        |
One {@class=bordered} |two {@class=bordered} |three {@class=bordered} |four {@class=bordered} |five {@class=bordered} |six {@class=bordered}

Colons can be used to align columns.

| Stripped Tables {@class=stripped$bordered} | Centered {@class=stripped$bordered} | Right aligned {@class=stripped$bordered} |
| --------------------------------- |:--------------------------:| -------------------------------:|
| Zebra ăĂîÎâÂşŞţŢșȘțȚ {@class=stripped}           | c1.2 {@class=stripped}     | $1600 {@class=stripped}         |
| Stripes {@class=stripped}         | c2.2 {@class=stripped}     |   $12 {@class=stripped}         |
| zebra stripes {@class=stripped$bordered}   | c2.3 {@class=stripped$bordered}     |    $1 {@class=stripped$bordered}         |

First Header  | Second Header
------------- | -------------
Content Cell  | Content Cell
Content Cell  | Content Cell

| Name {@class=pbordered}         | Description {@class=pbordered}              |
| ------------- | ----------------------- |
| Help {@class=pbordered}         | Display the help window. {@class=pbordered} |
| Close {@class=pbordered}        | Closes a window {@class=pbordered}          |

| Name | Description |
| ----- | ----- |
| Action ^Help^ | ~~Display the ăĂîÎâÂşŞţŢșȘțȚ~~ help **window**.|
| Action ~Close~ | _Closes_ a window |

| Left-Aligned {@class=dbordered}  | Center Aligned {@class=dbordered}  | Right Aligned {@class=dbordered} |
| :------------ |:---------------:| -----:|
| col 3 is {@class=dbordered}      | some wordy text {@class=dbordered} | $1600 {@class=dbordered} |
| col 2 is {@class=dbordered}      | centered {@class=dbordered}        |   $12 {@class=dbordered} |
| zebra stripes {@class=dbordered} | are neat {@class=dbordered}        |    $1 {@class=dbordered} |

The outer pipes (|) are optional, and you don't need to make the raw Markdown line up prettily. You can also use inline Markdown.

Markdown {@class=dbordered} | Less {@class=dbordered} | Pretty {@class=dbordered}
--- | --- | ---
*Still* {@class=dbordered} | `renders` {@class=dbordered} | **nicely** {@class=dbordered}
1 {@class=dbordered} | 2 {@class=dbordered} | 3 {@class=dbordered}

> Blockquotes are very handy in email to emulate reply text.
> This line is part of the same quote.

Quote break.

> This is a very long line that will still be quoted properly when it wraps. Oh boy let's keep writing to make sure this is long enough to actually wrap for everyone. Oh, you can *put* **Markdown** into a blockquote.

### Use Hypens, Asterisks and Underscores

\-\-\-
\_\_\_
\*\*\*

### Horizontal Rules

- - -
Hyphens
---

***
Asterisks
* * *

Underscores
___

Here's a line for us to start with.

This line is separated from the one above by two newlines, so it will be a *separate paragraph*.

This line is also a separate paragraph, but...
This line is only separated by a single newline, so it's a separate line in the *same paragraph*.

[![IMAGE ALT TEXT HERE](//img.youtube.com/vi/4rUrYN4cnGs/0.jpg)](//www.youtube.com/watch?v=4rUrYN4cnGs "My Title"){.video-link #link .Extra-Class @target=_blank}

Fenced code blocks

```javascript
function test() {
  console.log("notice the blank line before this function?");
}
```

Set in stone
------------

Preformatted blocks are useful for ASCII art:

```
             ,-.
    ,     ,-.   ,-.
   / \   (   )-(   )
   \ |  ,.>-(   )-<
    \|,' (   )-(   )
     Y ___`-'   `-'
     |/__/   `-'
     |
     |
     |    -hrr-
  ___|_____________
```

External linking action
--------------------

I get 10 times more traffic from [Google] [1] than from
[Yahoo] [2] or [MSN] [3].

  [1]: http://google.com/        "Google"
  [2]: http://search.yahoo.com/  "Yahoo Search"
  [3]: http://search.msn.com/    "MSN Search"


[](#) {@id=anchor}

## Sample Data Definition

```html
<dl>
  <dt>Definition list</dt>
  <dd>Is something people use sometimes.</dd>

  <dt>Markdown in HTML</dt>
  <dd>Does *not* work **very** well. Use HTML <em>tags</em>.</dd>
</dl>
```

**Below should be rendered as plain HTML (HTML tags disallowed in this Markdown ...)**
<dl>
  <dt>Definition list</dt>
  <dd>Is something people use sometimes.</dd>

  <dt>Markdown in HTML</dt>
  <dd>Does *not* work **very** well. Use HTML <em>tags</em>.</dd>
</dl>