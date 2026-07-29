<!-- Bot traps — same three signals the contact form uses. The parent <form>
     must carry a data-nonce="…" attribute for the js_token field below to
     mean anything; assets/js/main.js fills it in for any form that has one. -->
<div class="form-hp" aria-hidden="true">
  <label for="website">Website</label>
  <input type="text" id="website" name="website" tabindex="-1" autocomplete="off" />
  <label for="company_url">Company URL</label>
  <input type="text" id="company_url" name="company_url" tabindex="-1" autocomplete="off" />
</div>
<input type="hidden" name="started_at" value="<?= time() ?>" />
<input type="hidden" name="js_token" value="" />
