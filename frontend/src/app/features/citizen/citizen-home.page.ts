import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-citizen-home-page',
  imports: [MatCardModule, MatIconModule, RouterLink],
  template: `
    <main class="page">
      <section class="hero">
        <p class="eyebrow">Portal ciudadano</p>
        <h1>Consulta y paga tus servicios municipales en linea.</h1>
        <p class="muted">Predial, agua potable y multas de transito desde cualquier dispositivo.</p>
      </section>

      <section class="card-grid">
        @for (item of cards; track item.route) {
          <a [routerLink]="item.route">
            <mat-card class="government-card service-card">
              <mat-icon>{{ item.icon }}</mat-icon>
              <h2>{{ item.title }}</h2>
              <p>{{ item.description }}</p>
            </mat-card>
          </a>
        }
      </section>
    </main>
  `,
  styles: [
    `
      .hero {
        padding: 18px 0 28px;
      }

      .eyebrow {
        color: var(--color-secondary);
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
      }

      h1 {
        max-width: 780px;
        margin: 0;
        font-size: clamp(2rem, 8vw, 4rem);
        line-height: 1;
      }

      .service-card {
        display: flex;
        flex-direction: column;
        gap: 8px;
        border: 1px solid rgba(15, 76, 129, 0.08);
      }

      mat-icon {
        width: 44px;
        height: 44px;
        font-size: 44px;
        color: var(--color-primary);
      }

      h2 {
        margin: 0;
      }

      p {
        margin: 0;
      }
    `,
  ],
})
export class CitizenHomePage {
  protected readonly cards = [
    {
      title: 'Consultar Predial',
      description: 'Busca por clave catastral y genera tu linea de captura.',
      icon: 'home_work',
      route: ['/consulta', 'predial'],
    },
    {
      title: 'Consultar Agua',
      description: 'Consulta adeudos por numero de contrato.',
      icon: 'water_drop',
      route: ['/consulta', 'agua'],
    },
    {
      title: 'Consultar Multas',
      description: 'Encuentra multas por folio o placa vehicular.',
      icon: 'traffic',
      route: ['/consulta', 'multas'],
    },
    {
      title: 'Mis Recibos',
      description: 'Descarga comprobantes emitidos por tus pagos.',
      icon: 'receipt_long',
      route: ['/recibos'],
    },
  ];
}
