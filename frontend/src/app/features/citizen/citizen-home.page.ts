import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-citizen-home-page',
  imports: [MatButtonModule, MatCardModule, MatIconModule, RouterLink],
  template: `
    <main class="page space-y-8">
      <section class="hero-panel grid gap-8 lg:grid-cols-[1.35fr_0.65fr]">
        <div class="relative z-10">
          <p class="section-eyebrow !text-white/80 before:!bg-white">Portal ciudadano</p>
          <h1 class="max-w-3xl text-4xl font-black leading-none tracking-tight sm:text-6xl">
            Paga tus servicios municipales con confianza y rapidez.
          </h1>
          <p class="mt-5 max-w-2xl text-base leading-7 text-white/78 sm:text-lg">
            Consulta adeudos de predial, agua potable y multas de transito desde una experiencia segura,
            mobile-first y preparada para cada municipio.
          </p>

          <div class="mt-7 flex flex-wrap gap-3">
            <span class="trust-badge"><mat-icon>verified_user</mat-icon> Pagos seguros</span>
            <span class="trust-badge"><mat-icon>receipt_long</mat-icon> Recibo oficial</span>
            <span class="trust-badge"><mat-icon>phone_iphone</mat-icon> Mobile first</span>
          </div>

          <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <a mat-flat-button class="corporate-button !bg-white !text-[var(--color-primary)]" routerLink="/consulta/predial">
              Consultar ahora
            </a>
            <a mat-button class="corporate-button !border !border-white/25 !text-white" routerLink="/recibos">
              Descargar recibo
            </a>
          </div>
        </div>

        <aside class="relative z-10 grid gap-3 self-end">
          @for (metric of metrics; track metric.label) {
            <div class="metric-pill">
              <strong class="block text-2xl font-black">{{ metric.value }}</strong>
              <span class="text-sm text-white/72">{{ metric.label }}</span>
            </div>
          }
        </aside>
      </section>

      <section class="grid gap-4 md:grid-cols-4">
        @for (step of steps; track step) {
          <div class="government-card !min-h-0 !p-5">
            <span class="grid size-10 place-items-center rounded-2xl bg-[var(--color-primary)]/10 text-sm font-black text-[var(--color-primary)]">
              {{ $index + 1 }}
            </span>
            <p class="mt-4 font-bold">{{ step }}</p>
          </div>
        }
      </section>

      <section>
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="section-eyebrow">Servicios disponibles</p>
            <h2 class="m-0 text-2xl font-black tracking-tight sm:text-3xl">Elige que quieres consultar</h2>
          </div>
          <p class="max-w-md text-sm leading-6 text-slate-500">
            Flujos simples para buscar adeudo, generar linea de captura, pagar y recibir comprobante.
          </p>
        </div>

        <div class="card-grid">
        @for (item of cards; track item.route) {
          <a [routerLink]="item.route" class="group">
            <mat-card class="government-card service-card transition duration-300 group-hover:-translate-y-1 group-hover:shadow-2xl">
              <span class="icon-shell">
                <mat-icon>{{ item.icon }}</mat-icon>
              </span>
              <div>
                <h3>{{ item.title }}</h3>
                <p>{{ item.description }}</p>
              </div>
              <span class="mt-auto inline-flex items-center gap-2 text-sm font-extrabold text-[var(--color-primary)]">
                Iniciar consulta <mat-icon class="!size-5 !text-xl">arrow_forward</mat-icon>
              </span>
            </mat-card>
          </a>
        }
        </div>
      </section>
    </main>
  `,
  styles: [
    `
      .service-card {
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        gap: 18px;
        min-height: 240px;
      }

      .service-card::after {
        position: absolute;
        top: -80px;
        right: -80px;
        width: 180px;
        height: 180px;
        content: "";
        border-radius: 999px;
        background: rgba(15, 76, 129, 0.08);
        transition: transform 220ms ease;
      }

      .service-card:hover::after {
        transform: scale(1.18);
      }

      .icon-shell {
        display: grid;
        width: 58px;
        height: 58px;
        place-items: center;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(15, 76, 129, 0.12), rgba(176, 141, 87, 0.18));
      }

      .icon-shell mat-icon {
        width: 34px;
        height: 34px;
        font-size: 34px;
        color: var(--color-primary);
      }

      h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 900;
      }

      p {
        margin: 0;
        color: var(--color-muted);
        line-height: 1.6;
      }
    `,
  ],
})
export class CitizenHomePage {
  protected readonly metrics = [
    { value: '24/7', label: 'Portal disponible' },
    { value: '3', label: 'Servicios integrados' },
    { value: 'PDF', label: 'Comprobante descargable' },
  ];

  protected readonly steps = ['Buscar', 'Consultar adeudo', 'Generar linea de captura', 'Pagar y descargar'];

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
