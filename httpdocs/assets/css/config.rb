require 'modular-scale'
require 'sassy-math'
require 'bootstrap-sass'

http_path = "/"
css_dir = "stylesheets"
sass_dir = "sass"
images_dir = "../../assets/images"

# You can select your preferred output style here (can be overridden via the command line):
# output_style = :expanded or :nested or :compact or :compressed
output_style = :compressed
environment	= :production
relative_assets = true
line_comments = false
cache_path = '/tmp/.sass-cache'
