var projects = [];
$('.project').each(function() {
  var project = {components: []};
  projects.push(project);
  var links    = $(this).find('a');
  project.name = links.first().text();
  project.name = $.trim(project.name);
  links.each(function(i) { i > 0 && project.components.push($.trim($(this).text())) });
});
console.log(JSON.stringify(projects, null, 4));

