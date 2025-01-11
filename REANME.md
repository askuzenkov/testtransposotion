# How to use it

## only 6 steps
- clone this repo
- run 
```shell
docker compose up -d
```
- open http://localhost:9081 in a browser
- if Symfony page is not showing run composer install command into example_php container
- copy json file into project/ directory
- run
```shell
docker exec -t example_php php bin/console app:note input.json 4
```
```
where 
- input.js - file which was copied on the previous step
- 4 - second parameter from test tssk (number of semitones)
just keep in mind that for negative value of second param should using '-- -4' format instead of '4'
```
- outputted file will be available on http://localhost:9081/tmp/out.json 
