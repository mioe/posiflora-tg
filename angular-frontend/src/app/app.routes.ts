import { Routes } from '@angular/router';
import { TelegramPageComponent } from './telegram/telegram-page';
import { ShopRedirectComponent } from './shop-redirect/shop-redirect';

export const routes: Routes = [
  { path: 'shops/:shopId/growth/telegram', component: TelegramPageComponent },
  { path: '**', component: ShopRedirectComponent },
];
