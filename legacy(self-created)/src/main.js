import { cityCenter, locations } from './locations.js';

const ACCEPTED_RADIUS_METERS = 900;
let mode = 'learn';
let done = [];
let active = null;
let guesses = [];
let revealed = false;
let map;
let layer;

function distanceMeters(a, b) {
  return L.latLng(a[0], a[1]).distanceTo(L.latLng(b[0], b[1]));
}

function pickNext(doneIds) {
  const pool = locations.filter((item) => !doneIds.includes(item.id));
  return pool[Math.floor(Math.random() * pool.length)] ?? null;
}

function escapeHtml(value) {
  return value.replace(/[&<>'"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[char]);
}

function renderMap() {
  layer.clearLayers();
  if (mode === 'learn') {
    locations.forEach((place) => {
      L.circleMarker(place.coords, { radius: 9, color: '#2563eb', fillColor: '#60a5fa', fillOpacity: 0.85 })
        .bindPopup(`<strong>${escapeHtml(place.name)}</strong><br>${escapeHtml(place.district)}<br>${escapeHtml(place.hint)}`)
        .addTo(layer);
    });
    return;
  }
  guesses.forEach((guess) => {
    L.circleMarker(guess.click, { radius: 7, color: guess.correct ? '#16a34a' : '#dc2626', fillOpacity: 0.9 })
      .bindPopup(`${escapeHtml(guess.place.name)}: ${Math.round(guess.distance)} м`).addTo(layer);
    L.circleMarker(guess.place.coords, { radius: 7, color: '#111827', fillColor: '#facc15', fillOpacity: 0.9 })
      .bindPopup(`Правильно: ${escapeHtml(guess.place.name)}`).addTo(layer);
    L.polyline([guess.click, guess.place.coords], { color: guess.correct ? '#16a34a' : '#dc2626', weight: 2, dashArray: '6 7' }).addTo(layer);
  });
  if (active && revealed) {
    L.circle(active.coords, { radius: ACCEPTED_RADIUS_METERS, color: '#2563eb', fillOpacity: 0.08 }).addTo(layer);
  }
}

function resetTest() {
  done = [];
  guesses = [];
  revealed = false;
  active = pickNext(done);
}

function handleGuess(click) {
  if (!active || mode !== 'test') return;
  const distance = distanceMeters(click, active.coords);
  guesses.unshift({ place: active, click, distance, correct: distance <= ACCEPTED_RADIUS_METERS });
  done.push(active.id);
  revealed = false;
  active = pickNext(done);
  render();
}

function render() {
  const score = guesses.filter((item) => item.correct).length;
  const root = document.querySelector('#root');
  root.innerHTML = `
    <main class="shell">
      <section class="panel">
        <p class="eyebrow">Миколаїв • урбаноніми</p>
        <h1>МикоГео</h1>
        <p class="lead">Мінімалістична гра у стилі Seterra Geography: вивчай і перевіряй місцевості Миколаєва на реальній OpenStreetMap-мапі.</p>
        <div class="tabs">
          <button id="learn" class="${mode === 'learn' ? 'active' : ''}">Навчання</button>
          <button id="test" class="${mode === 'test' ? 'active' : ''}">Тест</button>
        </div>
        ${mode === 'learn' ? learningTemplate() : testTemplate(score)}
      </section>
      <div id="map" aria-label="Інтерактивна мапа Миколаєва"></div>
    </main>`;

  document.querySelector('#learn').addEventListener('click', () => { mode = 'learn'; render(); });
  document.querySelector('#test').addEventListener('click', () => { mode = 'test'; resetTest(); render(); });
  document.querySelector('#hint')?.addEventListener('click', () => { revealed = !revealed; render(); });
  document.querySelector('#reset')?.addEventListener('click', () => { resetTest(); render(); });
  mountMap();
}

function learningTemplate() {
  return `<div class="card"><h2>Режим навчання</h2><p>Натискай маркери, щоб побачити правильну назву, район і коротку підказку. У наборі ${locations.length} урбанонімів.</p><ul class="chips">${locations.map((place) => `<li>${escapeHtml(place.name)}</li>`).join('')}</ul></div>`;
}

function testTemplate(score) {
  return `<div class="card"><h2>${active ? `Знайди: ${escapeHtml(active.name)}` : 'Раунд завершено'}</h2><p>${active ? (revealed ? escapeHtml(active.hint) : 'Клікни по мапі там, де розташована ця місцевість.') : `Результат: ${score} з ${locations.length}. Нижче видно помилки та відстані.`}</p><div class="actions"><button id="hint" ${active ? '' : 'disabled'}>Підказка / зона</button><button id="reset">Почати знову</button></div><p class="score">Влучань: ${score} / ${guesses.length}. Допуск: ${ACCEPTED_RADIUS_METERS} м.</p><ol class="results">${guesses.map((g) => `<li class="${g.correct ? 'ok' : 'bad'}">${escapeHtml(g.place.name)}: ${g.correct ? 'так' : 'ні'}, похибка ${Math.round(g.distance)} м</li>`).join('')}</ol></div>`;
}

function mountMap() {
  if (map) map.remove();
  map = L.map('map', { zoomControl: true }).setView(cityCenter, 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
  layer = L.layerGroup().addTo(map);
  map.on('click', (event) => handleGuess([event.latlng.lat, event.latlng.lng]));
  renderMap();
}

render();
export { ACCEPTED_RADIUS_METERS, distanceMeters, pickNext };
