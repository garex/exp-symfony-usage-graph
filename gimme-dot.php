<?php

$projects = json_decode(file_get_contents('projects-components.json'), true);

echo "digraph SymfonyComponents {
  rankdir=LR
  concentrate=true
";
foreach ($projects as $project) {
  extract($project);
  foreach ($components as $component) {
    echo "  \"$name\" -> \"$component\"\n";
  }
}

echo "} \n";

