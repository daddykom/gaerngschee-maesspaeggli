import { inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { Store } from '@ngrx/store';
import { of } from 'rxjs';
import { catchError, map, switchMap, withLatestFrom } from 'rxjs/operators';
import { Offer } from '../../shared/models/offer.model';
import { OffersService } from '../../shared/services/offers.service';
import { OffersActions } from './offers.actions';
import { selectCurrentPosition } from './offers.feature';

export const loadOffersEffect = createEffect(
  (actions$ = inject(Actions), store = inject(Store), offersService = inject(OffersService)) => {
    return actions$.pipe(
      ofType(OffersActions.loadOffers),
      withLatestFrom(store.select(selectCurrentPosition)),
      switchMap(([, currentPosition]) =>
        offersService.getOffers().pipe(
          map((offers) =>
            offers.map(
              (o): Offer => ({
                ...o,
                location: {
                  latitude: o.location.latitude,
                  longitude: o.location.longitude,
                  address: o.location.address,
                },
                currentDistance: 0,
                createdAt: new Date(o.createdAt),
                updatedAt: new Date(o.updatedAt),
              }),
            ),
          ),
          map((offers: Offer[]) =>
            offers.toSorted((a, b) => a.currentDistance - b.currentDistance),
          ),
          map((offers: Offer[]) => OffersActions.loadOffersSuccess({ offers })),
          catchError((error) => of(OffersActions.loadOffersFailure({ error: error.message }))),
        ),
      ),
    );
  },
  { functional: true },
);

export const offersEffects = {
  loadOffersEffect,
};
