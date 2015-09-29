<?php

$json = json_decode(file_get_contents('projects-components.json'), true);

$transparency = transparency(0);

function transparency($percents) {
    return sprintf('%02X', 255 * ((100-$percents)/100));
}
/*
  epsilon=.001
  splines=true
  sep=0.05
  overlap=\"20:\"

  splines=true
  overlap=\"20:\"
*/

echo "digraph SymfonyComponents {
  layout=fdp
  overlap=false
  splines=polyline
  splines=true
  fontname=\"Ubuntu\"
  edge [
	color=\"#6F715C\"
	penwidth=0.8
	arrowhead=open
  ]
  node [style=\"rounded,filled\" fontname=\"Ubuntu\"]
";

$projects   = [];
$components = [];
$projectComponentsMax = 0;
$componentProjectsMax = 0;
foreach ($json as $project) {
    $projectComponentsMax = max($projectComponentsMax, count($project['components']));
    foreach ($project['components'] as $component) {
        if (!isset($components[$component])) {
            $components[$component] = ['projects' => [], 'index' => count($components)];
        }
        $components[$component]['projects'][] = $project['name'];
        $componentProjectsMax = max($componentProjectsMax, count($components[$component]['projects']));
    }
    $projects[$project['name']] = $project;
}

foreach ($projects as $name => $project) {
    $project['coupling'] = round(count($project['components']) / $projectComponentsMax, 2);
    $projects[$name] = $project;
}

foreach ($components as $name => $component) {
    $component['coupling'] = round(count($component['projects']) / $componentProjectsMax, 2);
    $components[$name] = $component;
}

uksort($components, function($k1, $k2) use($components) {
    $c1 = $components[$k1]['coupling'];
    $c2 = $components[$k2]['coupling'];
    if ($c1 == $c2) {
        return 0;
    }
    return ($c1 < $c2) ? 1 : -1;
});

$i = 0;
foreach ($components as $name => $component) {
    $components[$name]['index'] = $i++;
}

function generateColors() {
    $result = [];
    $hues = [
	    ['hue' => 0],
	    ['hue' => 39],
	    ['hue' => 60],
	    ['hue' => 120],
	    ['hue' => 180],
	    ['hue' => 240],
	    ['hue' => 300]
    ];

    $lightnessSaturations = [
	    ['saturation' => 60, 'lightness' => 50],
	    ['saturation' => 100, 'lightness' => 50],
	    ['saturation' => 100, 'lightness' => 80],
	    ['saturation' => 60, 'lightness' => 80],
    ];

    $lightnessValues = [
	    ['saturation' => 0.75, 'value' => 0.8],
	    ['saturation' => 1, 'value' => 1],
	    ['saturation' => 0.4, 'value' => 1],
	    ['saturation' => 0.26, 'value' => 0.92],
    ];

    for ($ls = 0; $ls < count($lightnessValues); $ls++) {
    for ($h = 0; $h < count($hues); $h++) {
        $color    = $hues[$h] + $lightnessValues[$ls];
        $color['hue'] = round($color['hue']/360, 3);
	    $result[] = $color;
    }}
    return $result;
}

$colors = generateColors();

$fullStackers = [];
foreach ($projects as $project) {
  $name = $project['name'];
  $fontsize = 7 + round(7 * $project['coupling']);
  $penwidth = 0.5 + 3 * $project['coupling'];
  $fillcolor = 'gray';
  if ($project['components'][0] == 'Symfony Full Stack') {
    $fillcolor = 'black';
    $fullStackers[] = $name;
  }
  echo "  \"$name\" [
    fillcolor=\"$fillcolor\"
    fontcolor=\"white\"
    shape=box
    fontsize=$fontsize
]
";
  foreach ($project['components'] as $component) {
    $i = $components[$component]['index'];
    $color = isset($colors[$i]) ? $colors[$i] : end($colors);
    extract($color, EXTR_OVERWRITE);
    echo "  \"$name\" -> \"$component\" [color=\"$hue $saturation $value\" penwidth=$penwidth]\n";
    if (isset($projects[$component])) {
        echo "  \"$component\" [fillcolor=\"$hue $saturation $value\"]\n";
        continue;
    }
    $fontsize = 10 + round(10 * $components[$component]['coupling']);
    $fontcolor = (isBlackFont($hue, $saturation, $value)) ? 'black' : 'white';

    echo "  \"$component\" [
        fillcolor=\"$hue $saturation $value\"
        fontcolor=\"$fontcolor\"
        shape=box
        fontsize=$fontsize
    ]\n";
  }
}

$color = $colors[$components['Symfony Full Stack']['index']];
extract($color, EXTR_OVERWRITE);
$fontcolor = (isBlackFont($hue, $saturation, $value)) ? 'black' : 'white';
echo "

    subgraph clusterWtf {
		graph [
			label=\"Full Stackers\"
			style=\"rounded,filled\"
			color=\"$hue $saturation $value\"
            fontcolor=\"$fontcolor\"
		]
";

foreach ($fullStackers as $fullStacker) {
    echo " \"$fullStacker\"
";
}

echo "
	}

}
";


/*
**  Converts HSV to RGB values
** –––––––––––––––––––––––––––––––––––––––––––––––––––––
**  Reference: http://en.wikipedia.org/wiki/HSL_and_HSV
**  Purpose:   Useful for generating colours with
**             same hue-value for web designs.
**  Input:     Hue        (H) Integer 0-360
**             Saturation (S) Integer 0-100
**             Lightness  (V) Integer 0-100
**  Output:    String "R,G,B"
**             Suitable for CSS function RGB().
*/
function fGetRGB($iH, $iS, $iV) {
    if($iH < 0)   $iH = 0;   // Hue:
    if($iH > 360) $iH = 360; //   0-360
    if($iS < 0)   $iS = 0;   // Saturation:
    if($iS > 100) $iS = 100; //   0-100
    if($iV < 0)   $iV = 0;   // Lightness:
    if($iV > 100) $iV = 100; //   0-100
    $dS = $iS/100.0; // Saturation: 0.0-1.0
    $dV = $iV/100.0; // Lightness:  0.0-1.0
    $dC = $dV*$dS;   // Chroma:     0.0-1.0
    $dH = $iH/60.0;  // H-Prime:    0.0-6.0
    $dT = $dH;       // Temp variable
    while($dT >= 2.0) $dT -= 2.0; // php modulus does not work with float
    $dX = $dC*(1-abs($dT-1));     // as used in the Wikipedia link
    switch($dH) {
        case($dH >= 0.0 && $dH < 1.0):
            $dR = $dC; $dG = $dX; $dB = 0.0; break;
        case($dH >= 1.0 && $dH < 2.0):
            $dR = $dX; $dG = $dC; $dB = 0.0; break;
        case($dH >= 2.0 && $dH < 3.0):
            $dR = 0.0; $dG = $dC; $dB = $dX; break;
        case($dH >= 3.0 && $dH < 4.0):
            $dR = 0.0; $dG = $dX; $dB = $dC; break;
        case($dH >= 4.0 && $dH < 5.0):
            $dR = $dX; $dG = 0.0; $dB = $dC; break;
        case($dH >= 5.0 && $dH < 6.0):
            $dR = $dC; $dG = 0.0; $dB = $dX; break;
        default:
            $dR = 0.0; $dG = 0.0; $dB = 0.0; break;
    }
    $dM  = $dV - $dC;
    $dR += $dM; $dG += $dM; $dB += $dM;
    $dR *= 255; $dG *= 255; $dB *= 255;
    return round($dR).",".round($dG).",".round($dB);
}

function isBlackFont($h, $s, $v) {
    list($r, $g, $b) = explode(',', fGetRGB($h * 360, $s * 100, $v * 100));
    return (($r*0.299 + $g*0.587 + $b*0.114) > 186);
}
