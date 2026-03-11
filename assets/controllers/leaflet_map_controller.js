import { Controller } from '@hotwired/stimulus';
import L from 'leaflet';
import 'leaflet/dist/leaflet.min.css';

export default class extends Controller {
    static values = {
        lat: Number,
        lng: Number,
        title: String,
    }

    connect() {
        const map = L.map(this.element).setView([this.latValue, this.lngValue], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const icon = L.divIcon({
            className: '',
            html: `<div style="
            width: 24px;
            height: 24px;
            background: #E8784A;
            border: 3px solid white;
            border-radius: 50%;
            box-shadow: 0 2px 6px rgba(0,0,0,0.4);
        "></div>`,
            iconSize: [24, 24],
            iconAnchor: [12, 12],
            popupAnchor: [0, -14],
        });

        L.marker([this.latValue, this.lngValue], { icon })
            .addTo(map)
            .bindPopup(this.titleValue)
            .openPopup();
    }
}
