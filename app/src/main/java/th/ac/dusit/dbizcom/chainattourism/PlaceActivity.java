package th.ac.dusit.dbizcom.chainattourism;

import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.Fragment;

import com.google.gson.Gson;

import java.util.Locale;

import th.ac.dusit.dbizcom.chainattourism.etc.Utils;
import th.ac.dusit.dbizcom.chainattourism.fragment.OtopListFragment;
import th.ac.dusit.dbizcom.chainattourism.fragment.OtopSearchFragment;
import th.ac.dusit.dbizcom.chainattourism.fragment.PlaceListFragment;
import th.ac.dusit.dbizcom.chainattourism.model.District;
import th.ac.dusit.dbizcom.chainattourism.model.Otop;
import th.ac.dusit.dbizcom.chainattourism.model.Place;

import static th.ac.dusit.dbizcom.chainattourism.PlaceDetailsActivity.KEY_OTOP_JSON;
import static th.ac.dusit.dbizcom.chainattourism.PlaceDetailsActivity.KEY_PLACE_JSON;

public class PlaceActivity extends BaseActivity implements
        PlaceListFragment.PlaceListFragmentListener,
        OtopSearchFragment.OtopSearchFragmentListener,
        OtopListFragment.OtopListFragmentListener {

    static final String KEY_PLACE_TYPE = "place_type";
    private static final String TAG_PLACE_LIST_FRAGMENT = "place_list_fragment";
    private static final String TAG_OTOP_SEARCH_FRAGMENT = "otop_search_fragment";
    private static final String TAG_OTOP_LIST_FRAGMENT = "otop_list_fragment";

    private Place.PlaceType mPlaceType;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_place);

        Intent intent = getIntent();
        mPlaceType = (Place.PlaceType) intent.getSerializableExtra(KEY_PLACE_TYPE);

        Fragment fragment = null;
        String tag = null;
        if (mPlaceType == Place.PlaceType.OTOP) {
            fragment = new OtopSearchFragment();
            tag = TAG_OTOP_SEARCH_FRAGMENT;
        } else {
            fragment = PlaceListFragment.newInstance(mPlaceType);
            tag = TAG_PLACE_LIST_FRAGMENT;
        }

        loadFragment(
                fragment,
                tag,
                false,
                FragmentTransitionType.NONE
        );
    }

    /*หน้าค้นหาแหล่งท่องเที่ยว, วัด, ร้านอาหาร*/
    @Override
    public void onClickPlace(Place place) {
        String msg = String.format(
                Locale.getDefault(),
                "%s\nอำเภอ: %s\nเบอร์โทร: %s\nพิกัด %.6f, %.6f\nType: %s",
                place.name, place.district, place.phone, place.latitude, place.longitude, place.placeType
        );
        //Utils.showShortToast(this, msg);

        Intent intent = new Intent(PlaceActivity.this, PlaceDetailsActivity.class);
        intent.putExtra(KEY_PLACE_JSON, new Gson().toJson(place));
        startActivity(intent);
    }

    /*หน้าค้นหาสินค้า OTOP*/
    @Override
    public void onClickDistrict(District district) {
        Utils.hideKeyboard(this);
        loadFragment(
                OtopListFragment.newInstance(district.name, false),
                TAG_OTOP_LIST_FRAGMENT,
                true,
                FragmentTransitionType.SLIDE
        );
    }

    @Override
    public void onSearchOtop(String searchTerm) {
        Utils.hideKeyboard(this);
        loadFragment(
                OtopListFragment.newInstance(searchTerm, true),
                TAG_OTOP_LIST_FRAGMENT,
                true,
                FragmentTransitionType.SLIDE
        );
    }

    /*หน้ารายการสินค้า OTOP*/
    @Override
    public void onClickOtop(Otop otop) {
        String msg = String.format(
                Locale.getDefault(),
                "%s\n%s, %s, %s\nเบอร์โทร: %s\nพิกัด %.6f, %.6f",
                otop.name, otop.village, otop.subDistrict, otop.district, otop.phone, otop.latitude, otop.longitude
        );
        Utils.showShortToast(this, msg);

        Intent intent = new Intent(PlaceActivity.this, PlaceDetailsActivity.class);
        intent.putExtra(KEY_OTOP_JSON, new Gson().toJson(otop));
        startActivity(intent);
    }
}
