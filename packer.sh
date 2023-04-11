#!/bin/bash
#yarn prod

get_env_var() {
  local var_name="$1"
  grep -o "${var_name}=.*" .env | sed 's/^.*=//; s/^"//; s/"$//'
}

replace_in_file() {
  local var_name="$1"
  local file_path="$2"
  local var_value="$(get_env_var "$var_name")"
  sed -i '' "s/{$var_name}/$var_value/" "$file_path"
}

replace_in_readme(){
  replace_in_file $1 "export/files/README.txt"
}

replace_in_plugin(){
  replace_in_file $1 "export/files/plugin.php"
}

#read .version file
export $(grep -v '^#' .version | xargs)
export $(grep -v '^#' .env | xargs)

rm -r export
mkdir "export"

SRCDIR="./"
DESDIR="export"

# Prepare build for Github
rsync -av \
--exclude=export \
--exclude=.env \
--exclude=.version \
--exclude=_versions.php \
--exclude=.git \
--exclude=.idea \
--exclude=packer.sh \
--exclude=package.json \
--exclude=yarn.lock \
--exclude=yarn-error.log \
--exclude=scripts \
--exclude=node_modules \
--exclude=.env.example \
--exclude=TODO.txt \
--exclude=Terms.md \
$SRCDIR $DESDIR/files

#generate script and replace with output in README.txt
sed -i '' "s/{CHANGE_LOG}/$(php ./scripts/generate-changelog.php | tr '\n' '\r')/" export/files/README.txt

replace_in_readme "ACOS_VERSION"
replace_in_plugin "ACOS_VERSION"

#Copy new files to SVN repo
rsync -av \
$DESDIR/files/* ~/projects/acos-svn/trunk