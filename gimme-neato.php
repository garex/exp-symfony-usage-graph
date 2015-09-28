<?php

$json = json_decode(file_get_contents('projects-components.json'), true);

$transparency = transparency(0);

function transparency($percents) {
    return sprintf('%02X', 255 * ((100-$percents)/100));
}

echo "digraph SymfonyComponents {
  graph [truecolor]
  epsilon=.001
  overlap=false
  splines=true
  sep=0.1
  outputorder=edgesfirst
  edge [color=\"#6F715C\"]
  node [style=\"rounded,filled\", color=\"#6F715C\", fontname=\"Ubuntu\"]
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
    return ($c1 > $c2) ? 1 : -1;
});

$i = count($components);
foreach ($components as $name => $component) {
    $components[$name]['index'] = --$i;
}

foreach ($projects as $project) {
  $name = $project['name'];
  $fontsize = 7 + round(7 * $project['coupling']);
  // #96E539$transparency
  $fillcolor = 'gray';
  if ($project['components'][0] == 'Symfony Full Stack') {
    $fillcolor = 'black';
  }
  echo "  \"$name\" [fillcolor=\"$fillcolor\", fontcolor=\"white\" shape=box  fontsize=$fontsize]\n";
  foreach ($project['components'] as $component) {
    $hue = round($components[$component]['index'] / count($components) * 0.83, 3);
    $hue2 = $hue + 0.33;
    if ($hue2 > 1) {
        $hue2--;
    }
    echo "  \"$name\" -> \"$component\" [color=\"$hue 0.900 0.900\"]\n";
    if (isset($projects[$component])) {
        echo "  \"$component\" [fillcolor=\"$hue 1.000 1.000\"]\n";
        continue;
    }
    $fontsize = 10 + round(10 * $components[$component]['coupling']);
    $fontcolor = ($hue > 0.517 && $hue < 0.8) ? 'white' : 'black';
    echo "  \"$component\" [fillcolor=\"$hue 1.000 1.000\" fontcolor=\"$fontcolor\" shape=box fontsize=$fontsize]\n";
  }
}

echo "} \n";

