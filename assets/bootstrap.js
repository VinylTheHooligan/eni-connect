import { Application } from '@hotwired/stimulus';
import PlaceController from './controllers/place_controller.js';
import LeafletMapController from './controllers/leaflet_map_controller.js';

export const application = Application.start();

application.register('place', PlaceController);
application.register('leaflet-map', LeafletMapController);
