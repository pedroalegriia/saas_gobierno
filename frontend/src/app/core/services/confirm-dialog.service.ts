import { Injectable, inject } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { map } from 'rxjs';
import { ConfirmDialogComponent, ConfirmDialogData } from '../../shared/confirm-dialog.component';

@Injectable({ providedIn: 'root' })
export class ConfirmDialogService {
  private readonly dialog = inject(MatDialog);

  confirm(data: ConfirmDialogData) {
    return this.dialog.open(ConfirmDialogComponent, {
      width: 'min(92vw, 420px)',
      data,
    }).afterClosed().pipe(map(Boolean));
  }
}
