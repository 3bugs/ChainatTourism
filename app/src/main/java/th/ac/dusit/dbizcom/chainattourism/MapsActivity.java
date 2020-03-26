package th.ac.dusit.dbizcom.chainattourism;

import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.support.v7.app.ActionBar;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;

import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.MarkerOptions;
import com.google.gson.Gson;

import java.util.Locale;

import th.ac.dusit.dbizcom.chainattourism.model.Otop;
import th.ac.dusit.dbizcom.chainattourism.model.Place;

import static th.ac.dusit.dbizcom.chainattourism.PlaceDetailsActivity.KEY_OTOP_JSON;
import static th.ac.dusit.dbizcom.chainattourism.PlaceDetailsActivity.KEY_PLACE_JSON;

public class MapsActivity extends AppCompatActivity implements OnMapReadyCallback {

    private static final String TAG = MapsActivity.class.getName();

    private GoogleMap mMap;
    private Place mPlace = null;
    private Otop mOtop = null;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_maps);

        Intent intent = getIntent();

        String placeJson = intent.getStringExtra(KEY_PLACE_JSON);
        String otopJson = intent.getStringExtra(KEY_OTOP_JSON);
        if (placeJson != null) {
            mPlace = new Gson().fromJson(placeJson, Place.class);
        } else {
            mOtop =  new Gson().fromJson(otopJson, Otop.class);
        }

        ActionBar actionBar = getSupportActionBar();
        if (actionBar != null) {
            actionBar.setTitle(mPlace != null ? mPlace.name : mOtop.name);
        }

        SupportMapFragment mapFragment = (SupportMapFragment) getSupportFragmentManager()
                .findFragmentById(R.id.map);
        if (mapFragment != null) {
            mapFragment.getMapAsync(this);
        }

        populateDetails();
    }

    private void populateDetails() {
        TextView nameTextView = findViewById(R.id.place_name_text_view);
        nameTextView.setText(mPlace != null ? mPlace.name : mOtop.name);

        TextView addressTextVew = findViewById(R.id.address_text_view);
        addressTextVew.setText(mPlace != null ? mPlace.address : mOtop.address);

        Button directionButton = findViewById(R.id.direction_button);
        directionButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                //geo:0,0?q=${latLng}(${label})
                String urlString = String.format(
                        Locale.getDefault(),
                        "geo:0,0?q=%f,%f(%s)",
                        (mPlace != null ? mPlace.latitude : mOtop.latitude),
                        (mPlace != null ? mPlace.longitude : mOtop.longitude),
                        (mPlace != null ? mPlace.name : mOtop.name)
                );
                Uri intentUri = Uri.parse(urlString);
                Intent mapIntent = new Intent(Intent.ACTION_VIEW, intentUri);
                mapIntent.setPackage("com.google.android.apps.maps");
                startActivity(mapIntent);
                Log.i(TAG, "URL: " + urlString);
            }
        });
    }

    @Override
    public void onMapReady(GoogleMap googleMap) {
        mMap = googleMap;

        LatLng latLng = new LatLng(mPlace != null ? mPlace.latitude : mOtop.latitude, mPlace != null ? mPlace.longitude : mOtop.longitude);
        mMap.addMarker(new MarkerOptions().position(latLng).title(mPlace != null ? mPlace.name : mOtop.name));
        mMap.animateCamera(CameraUpdateFactory.newLatLngZoom(latLng, 15));
    }
}
