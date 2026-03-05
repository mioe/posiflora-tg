import { Routes } from '@angular/router';
import { TelegramPageComponent } from './telegram/telegram-page';

export const routes: Routes = [
  { path: 'shops/:shopId/growth/telegram', component: TelegramPageComponent },
  { path: '**', redirectTo: 'shops/1/growth/telegram' },
];
