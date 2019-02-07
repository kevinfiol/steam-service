const fs = require('fs');
const heroes_json    = fs.readFileSync(__dirname + '/heroes.json', 'utf8');
const heroStats_json = fs.readFileSync(__dirname + '/heroStats.json', 'utf8');

const heroes = {};
const path = __dirname + '/../data/heroes.json';

let raw = JSON.parse(heroes_json);

for (let h of raw) {
    const id = h.id;
    delete h.id;
    heroes[id] = h;
}

raw = JSON.parse(heroStats_json);

for (let h of raw) {
    heroes[h.id].img  = h.img;
    heroes[h.id].icon = h.icon;
}

fs.writeFile(path, JSON.stringify(heroes), err => {
    if (err) console.log(err);
    else console.log(`File saved to ${path}`);
});