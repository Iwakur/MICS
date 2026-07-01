import test from 'node:test';
import assert from 'node:assert/strict';
import { locations } from '../src/locations.js';

test('location dataset has unique valid Mykolaiv entries', () => {
  assert.equal(locations.length, 15);
  assert.equal(new Set(locations.map((place) => place.id)).size, locations.length);
  for (const place of locations) {
    assert.match(place.name, /[А-ЯІЇЄҐа-яіїєґ0-9]/);
    assert.ok(place.coords[0] > 46.88 && place.coords[0] < 47.08, `${place.name} latitude`);
    assert.ok(place.coords[1] > 31.88 && place.coords[1] < 32.12, `${place.name} longitude`);
  }
});
