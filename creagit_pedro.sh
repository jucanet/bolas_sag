rm -rf .git/
touch README.md
git init
git config --global user.name 'cuenta_software'
git config --global user.email pvasquez.trebol@gmail.com
git add README.md
git add --all
git commit -m "subida inicial"
git remote add origin git@github.com:ppvasqueze/bolas_sag
git push -u origin master
