

guard 'livereload' do
  watch(%r{.+\.html})
  watch(%r{.+\.php})
  watch(%r{htdocs/styles/.+\.css})
  watch(%r{htdocs/scripts/.+\.js})
end

guard 'compass', :configuration_file => 'support/sass-config.rb' do
  watch(%r{support/sass/(.*)\.s[ac]ss})
end

guard 'coffeescript', :input => 'support/coffeescripts', :output => 'static/scripts'
