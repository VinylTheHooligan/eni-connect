import { Application } from '@hotwired/stimulus';
import PlaceController from './controllers/place_controller.js';

export const application = Application.start();

// Registre des controlleurs
application.register('place', PlaceController);
console.log(application);
