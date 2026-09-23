<div class="save-dock">
    <div class="field">
        <label for="change-summary">Resumen del cambio <span class="muted">(opcional)</span></label>
        <input id="change-summary" type="text" name="change_summary" value="{{ old('change_summary') }}" placeholder="Describe qué actualizaste" maxlength="1000">
    </div>
    <div class="save-actions">
        <span class="save-status" data-save-status role="status">Sin cambios pendientes</span>
        <button type="submit" class="button button-primary">Guardar cambios</button>
    </div>
</div>
