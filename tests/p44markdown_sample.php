<?php

require __DIR__ . '/vendor/autoload.php';

$demo = '# ' . date('Y-m-d H:m:s') . "\n" . <<<'DEMO'
# Header1
## Header2

- blubb = {% $blubb %}
- gugus = {% $gugus %}
- calc = {% 78/9 %}
- try a _underscore text_
- try a *italic text*
- try a **bold text**
- try a _(red)red text_
- try a _(blue)blue *italic* **bold** text_
- try <span style="font-size: 24px;">_(red)BIG_</span>

{% if $blubb=='varblubb' %}
{% if !$gugus %}
Gugus is FALSE.
{% else if $gugus>3 %}
Gugus is larger than 3 ({% $gugus %}).
{% elsif $gugus>2 %}
Gugus is larger than 2 ({% $gugus %}).
{% else %}
Gugus is {% $gugus %}.
{% endif %}
{% else %}
blubb is not varblubb.
{% endif %}
DEMO;

$defs = array(
  'blubb' => 'varblubb',
  'gugus' => 1,
);

$parser = new \cebe\markdown\p44Markdown();
$parser->add_defs($defs);
echo $parser->parse($demo);

?>
